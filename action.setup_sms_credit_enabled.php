<?php
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::USE_PERM)) return;

if (isset($params['cancel'])) {
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$uid = get_userid();

// Handle resend code
if (isset($params['resend_code'])) {
    $pending = TwoFactorUserMeta::get($uid, 'sms_phone_pending');
    if ($pending) {
        $license_key = get_site_preference('twofactor_sms_product_key', '');
        $config = cms_utils::get_config();
        $domain = parse_url($config['root_url'], PHP_URL_HOST);
        
        if ($license_key) {
            $result = TwoFactorAPI::send_verification($license_key, $domain, $pending);
            
            if (isset($result['success']) && $result['success']) {
                $message = ['class' => 'pagemcontainer', 'text' => $this->Lang('verification_sent')];
            } else {
                $error_message = $result['error'] ?? 'Unknown error';
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
        $license_key = get_site_preference('twofactor_sms_product_key', '');
        $config = cms_utils::get_config();
        $domain = parse_url($config['root_url'], PHP_URL_HOST);
        
        $result = TwoFactorAPI::verify_code($license_key, $domain, $pending, $code);
        
        if (isset($result["approved"]) && $result["approved"] === true) {
            TwoFactorUserMeta::update($uid, 'sms_phone', $pending);
            TwoFactorUserMeta::delete($uid, 'sms_phone_pending');
            TwoFactorCore::enable_provider_for_user($uid, 'TwoFactorProviderSMS');
            $this->SetMessage($this->Lang('sms_enabled'));
            $this->RedirectToAdminTab('','','user_prefs');
            return;
        } else {
            $error_message = $result['error'] ?? 'Invalid code';
            $message = ['class' => 'pageerrorcontainer', 'text' => $error_message];
            audit($uid, $this->GetName(), 'SMS verification failed: ' . $error_message);
        }
    }
}

// Handle send verification
if (isset($params['send_verification'])) {
    $phone = trim($params['phone'] ?? '');
    if ($phone) {
        $license_key = get_site_preference('twofactor_sms_product_key', '');
        $config = cms_utils::get_config();
        $domain = parse_url($config['root_url'], PHP_URL_HOST);
        
        if ($license_key) {
            $normalized_phone = TwoFactorAPI::normalize_phone($phone);
            $result = TwoFactorAPI::send_verification($license_key, $domain, $phone);
            
            if (isset($result['success']) && $result['success']) {
                TwoFactorUserMeta::update($uid, 'sms_phone_pending', $normalized_phone);
                $message = ['class' => 'pagemcontainer', 'text' => $this->Lang('verification_sent')];
            } else {
                $error_message = $result['error'] ?? 'Unknown error';
                $message = ['class' => 'pageerrorcontainer', 'text' => $error_message];
                audit($uid, $this->GetName(), 'SMS send verification failed: ' . $error_message);
            }
        } else {
            $message = ['class' => 'pageerrorcontainer', 'text' => 'SMS credits not configured'];
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

$product_key = get_site_preference('twofactor_sms_product_key', '');
$credits_configured = !empty($product_key);
$credits_remaining = 0;
$license_plan = '';

if (!empty($product_key)) {
    $domain = $_SERVER['HTTP_HOST'];
    $result = TwoFactorAPI::validate_license($product_key, $domain);
    
    if ($result && isset($result['valid']) && $result['valid'] === true) {
        $credits_remaining = isset($result['credits_remaining']) ? $result['credits_remaining'] : 0;
        $license_plan = isset($result['plan']) ? $result['plan'] : '';
    }
}

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_sms_credit_enabled.tpl'), null, null, $smarty);
$tpl->assign('product_key', $product_key);
$tpl->assign('credits_remaining', $credits_remaining);
$tpl->assign('license_plan', $license_plan);
$tpl->assign('phone', $phone);
$tpl->assign('is_enabled', $is_enabled);
$tpl->assign('pending_phone', $pending_phone);
$tpl->assign('credits_configured', $credits_configured);
$tpl->assign('message', $message ?? '');
$tpl->display();
