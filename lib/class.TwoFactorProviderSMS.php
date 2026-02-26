<?php
# See doc/LICENSE.txt for full license information.
# See doc/LICENSE.txt for full license information.
class TwoFactorProviderSMS extends TwoFactorProvider
{
    private static $instance;

    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_key()
    {
        return 'TwoFactorProviderSMS';
    }

    public function get_label()
    {
        $mod = cms_utils::get_module('TwoFactor');
        return $mod->Lang('provider_sms');
    }

    public function is_available_for_user($user_id)
    {
        $phone = TwoFactorUserMeta::get($user_id, 'sms_phone');
        $mod = cms_utils::get_module('TwoFactor');
        $sms_available = $mod->GetPreference('twofactor_sms_available', false);
        
        return !empty($phone) && $sms_available == true;
    }

    public function generate_and_send_code($user_id)
    {
        $phone = TwoFactorUserMeta::get($user_id, 'sms_phone');
        $mod = cms_utils::get_module('TwoFactor');
        $smscredit_enabled = $mod->GetPreference('twofactor_smscredit_enabled', false);
        
        // Prioritize SMS credits if enabled
        if ($smscredit_enabled) {
            $license_key = $mod->GetPreference('twofactor_sms_product_key', '');
            $config = cms_utils::get_config();
            $domain = parse_url($config['root_url'], PHP_URL_HOST);
            
            if ($license_key) {
                $result = TwoFactorAPI::send_verification($license_key, $domain, $phone);
                return isset($result['success']) && $result['success'];
            }
        }
        
        // Fall back to Twilio API
        $api_key_sid = $mod->GetPreference('twofactor_twilio_api_key');
        $api_secret = $mod->GetPreference('twofactor_twilio_api_secret');
        $service_sid = $mod->GetPreference('twofactor_twilio_service_sid');
        
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
        
        return $http_code >= 200 && $http_code < 300;
    }

    public function validate_authentication($user_id, $params = [])
    {
        $submitted_code = $this->sanitize_code($params['authcode'] ?? '', 6);
        if (!$submitted_code) return false;
        
        $phone = TwoFactorUserMeta::get($user_id, 'sms_phone');
        $mod = cms_utils::get_module('TwoFactor');
        $smscredit_enabled = $mod->GetPreference('twofactor_smscredit_enabled', false);
        
        // Prioritize SMS credits if enabled
        if ($smscredit_enabled) {
            $license_key = $mod->GetPreference('twofactor_sms_product_key', '');
            $config = cms_utils::get_config();
            $domain = parse_url($config['root_url'], PHP_URL_HOST);
            
            if ($license_key) {
                $result = TwoFactorAPI::verify_code($license_key, $domain, $phone, $submitted_code);
                return isset($result['approved']) && $result['approved'] === true;
            }
        }
        
        // Fall back to Twilio API
        $api_key_sid = $mod->GetPreference('twofactor_twilio_api_key');
        $api_secret = $mod->GetPreference('twofactor_twilio_api_secret');
        $service_sid = $mod->GetPreference('twofactor_twilio_service_sid');
        
        $url = "https://verify.twilio.com/v2/Services/{$service_sid}/VerificationCheck";
        
        $data = [
            'To' => $phone,
            'Code' => $submitted_code
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$api_key_sid}:{$api_secret}");
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            $response = json_decode($result, true);
            return isset($response['status']) && $response['status'] === 'approved';
        }
        
        return false;
    }

    public function user_setup_form($user_id)
    {
        return '';
    }

    public function authentication_page($user_id)
    {
        return '';
    }
    
    public static function verify_twilio_credentials($api_key, $api_secret, $service_sid)
    {
        $url = "https://verify.twilio.com/v2/Services/{$service_sid}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$api_key}:{$api_secret}");
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $http_code == 200;
    }
}
