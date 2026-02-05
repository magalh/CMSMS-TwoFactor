<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::USE_PERM)) return;

if (isset($params['cancel'])) {
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$uid = get_userid();
$provider = TwoFactorProviderSMS::get_instance();

function get_friendly_twilio_error($response) {
    $code = $response['code'] ?? 0;
    $errors = [
        20404 => 'Invalid Twilio Service SID. Please check your Verify Service SID in settings.',
        20003 => 'Invalid API credentials. Please check your API Key SID and Secret.',
        60200 => 'Invalid phone number format. Use E.164 format (e.g., +1234567890).',
        60202 => 'Maximum verification attempts reached. Please try again later.',
        60203 => 'Maximum verification checks reached. Please request a new code.',
        60205 => 'SMS is not supported for this phone number.',
        60212 => 'Too many requests. Please wait before trying again.',
    ];
    return $errors[$code] ?? ($response['message'] ?? 'Unknown error');
}

// Handle resend code
if (isset($params['resend_code'])) {
    $pending = TwoFactorUserMeta::get($uid, 'sms_phone_pending');
    if ($pending) {
        $api_key_sid = get_site_preference('twofactor_twilio_api_key');
        $api_secret = get_site_preference('twofactor_twilio_api_secret');
        $service_sid = get_site_preference('twofactor_twilio_service_sid');
        
        if ($api_key_sid && $api_secret && $service_sid) {
            $url = "https://verify.twilio.com/v2/Services/{$service_sid}/Verifications";
            
            $data = [
                'To' => $pending,
                'Channel' => 'sms'
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "{$api_key_sid}:{$api_secret}");
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $response = json_decode($result, true);
            
            if ($http_code >= 200 && $http_code < 300) {
                $message = ['class' => 'pagemcontainer', 'text' => $this->Lang('verification_sent')];
            } else {
                $error_message = get_friendly_twilio_error($response);
                $message = ['class' => 'pageerrorcontainer', 'text' => $error_message];
                audit($uid, $this->GetName(), 'SMS resend verification failed: ' . $error_message);
            }
        }
    }
}

// Handle change phone
if (isset($params['change_phone'])) {
    TwoFactorUserMeta::delete($uid, 'sms_phone_pending');
}

// Handle verify code
if (isset($params['verify_code'])) {
    $code = trim($params['code'] ?? '');
    $pending = TwoFactorUserMeta::get($uid, 'sms_phone_pending');
    
    if ($code && $pending) {
        $api_key_sid = get_site_preference('twofactor_twilio_api_key');
        $api_secret = get_site_preference('twofactor_twilio_api_secret');
        $service_sid = get_site_preference('twofactor_twilio_service_sid');
        
        $url = "https://verify.twilio.com/v2/Services/{$service_sid}/VerificationCheck";
        
        $data = [
            'To' => $pending,
            'Code' => $code
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$api_key_sid}:{$api_secret}");
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response = json_decode($result, true);
        
        if ($http_code >= 200 && $http_code < 300 && $response['status'] === 'approved') {
            TwoFactorUserMeta::update($uid, 'sms_phone', $pending);
            TwoFactorUserMeta::delete($uid, 'sms_phone_pending');
            TwoFactorCore::enable_provider_for_user($uid, 'TwoFactorProviderSMS');
            $this->SetMessage($this->Lang('sms_enabled'));
            $this->RedirectToAdminTab('','','user_prefs');
            return;
        } else {
            $error_message = get_friendly_twilio_error($response);
            $message = ['class' => 'pageerrorcontainer', 'text' => $error_message];
            audit($uid, $this->GetName(), 'SMS verification failed: ' . $error_message);
        }
    }
}

// Handle send verification
if (isset($params['send_verification'])) {
    $phone = trim($params['phone'] ?? '');
    if ($phone) {
        $api_key_sid = get_site_preference('twofactor_twilio_api_key');
        $api_secret = get_site_preference('twofactor_twilio_api_secret');
        $service_sid = get_site_preference('twofactor_twilio_service_sid');
        
        if ($api_key_sid && $api_secret && $service_sid) {
            $url = "https://verify.twilio.com/v2/Services/{$service_sid}/Verifications";
            
            $data = [
                'To' => $phone,
                'Channel' => 'sms'
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "{$api_key_sid}:{$api_secret}");
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $response = json_decode($result, true);
            
            if ($http_code >= 200 && $http_code < 300) {
                TwoFactorUserMeta::update($uid, 'sms_phone_pending', $phone);
                $message = ['class' => 'pagemcontainer', 'text' => $this->Lang('verification_sent')];
            } else {
                $error_message = get_friendly_twilio_error($response);
                $message = ['class' => 'pageerrorcontainer', 'text' => $error_message];
                audit($uid, $this->GetName(), 'SMS send verification failed: ' . $error_message);
            }
        }
    }
}

// Handle disable
if (isset($params['disable'])) {
    TwoFactorCore::disable_provider_for_user($uid, 'TwoFactorProviderSMS');
    TwoFactorUserMeta::delete($uid, 'sms_phone');
    TwoFactorUserMeta::delete($uid, 'sms_phone_pending');
    $this->SetMessage($this->Lang('sms_disabled'));
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$phone = TwoFactorUserMeta::get($uid, 'sms_phone');
$is_enabled = !empty($phone);
$pending_phone = TwoFactorUserMeta::get($uid, 'sms_phone_pending');

$twilio_api_key = get_site_preference('twofactor_twilio_api_key', '');
$twilio_api_secret = get_site_preference('twofactor_twilio_api_secret', '');
$twilio_service_sid = get_site_preference('twofactor_twilio_service_sid', '');
$twilio_configured = !empty($twilio_api_key) && !empty($twilio_api_secret) && !empty($twilio_service_sid);

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_sms.tpl'), null, null, $smarty);
$tpl->assign('phone', $phone);
$tpl->assign('is_enabled', $is_enabled);
$tpl->assign('pending_phone', $pending_phone);
$tpl->assign('twilio_configured', $twilio_configured);
$tpl->assign('message', $message ?? '');
$tpl->display();
