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
        $api_key_sid = get_site_preference('twofactor_twilio_api_key_sid');
        $api_secret = get_site_preference('twofactor_twilio_api_secret');
        $service_sid = get_site_preference('twofactor_twilio_service_sid');
        
        return !empty($phone) && !empty($api_key_sid) && !empty($api_secret) && !empty($service_sid);
    }

    public function generate_and_send_code($user_id)
    {
        $phone = TwoFactorUserMeta::get($user_id, 'sms_phone');
        $api_key_sid = get_site_preference('twofactor_twilio_api_key_sid');
        $api_secret = get_site_preference('twofactor_twilio_api_secret');
        $service_sid = get_site_preference('twofactor_twilio_service_sid');
        
        // Start verification using Twilio Verify API
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

    public function validate_authentication($user_id)
    {
        $submitted_code = preg_replace('/\s+/', '', $_POST['authcode'] ?? '');
        $phone = TwoFactorUserMeta::get($user_id, 'sms_phone');
        $api_key_sid = get_site_preference('twofactor_twilio_api_key_sid');
        $api_secret = get_site_preference('twofactor_twilio_api_secret');
        $service_sid = get_site_preference('twofactor_twilio_service_sid');
        
        // Check verification using Twilio Verify API
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
}
