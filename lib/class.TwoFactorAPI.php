<?php
class TwoFactorAPI
{
    const API_BASE_URL = 'https://api.pixelsolutions.biz';
    
    public static function validate_license($license_key, $domain)
    {
        if (empty($license_key) || strlen($license_key) < 10) {
            return ['valid' => false, 'error' => 'Invalid license key format'];
        }
        
        $api_url = self::API_BASE_URL . '/licenses/validate';
        
        $data = json_encode([
            'license_key' => $license_key,
            'domain' => self::normalize_domain($domain)
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            return ['valid' => false, 'error' => 'Connection error: ' . $curl_error];
        }
        
        if ($http_code !== 200) {
            $error_result = json_decode($response, true);
            if ($error_result && isset($error_result['error'])) {
                return $error_result;
            }
            return ['valid' => false, 'error' => 'API error: ' . $http_code];
        }
        
        if ($response) {
            $result = json_decode($response, true);
            if (is_array($result)) {
                return $result;
            }
        }
        
        return ['valid' => false, 'error' => 'Invalid API response'];
    }
    
    public static function send_verification($license_key, $domain, $phone, $country = null)
    {
        $api_url = self::API_BASE_URL . '/verification/send';
        
        $normalized_phone = self::normalize_phone($phone);
        
        if (!self::validate_phone_format($normalized_phone)) {
            return ['success' => false, 'error' => 'Invalid phone number format'];
        }
        
        if (!$country) {
            $country = self::get_country_from_phone($normalized_phone);
            if (!$country) {
                return ['success' => false, 'error' => 'Country not supported'];
            }
        }
        
        $data = json_encode([
            'license_key' => $license_key,
            'domain' => self::normalize_domain($domain),
            'phone' => $normalized_phone,
            'country' => $country
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response) {
            return json_decode($response, true);
        }
        
        return ['success' => false, 'error' => 'API request failed'];
    }
    
    public static function normalize_phone($phone)
    {
        $phone = preg_replace('/\s+/', '', $phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (strpos($phone, '+') !== 0) {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
    
    public static function validate_phone_format($phone)
    {
        return preg_match('/^\+[1-9]\d{1,14}$/', $phone) === 1;
    }
    
    public static function get_country_from_phone($phone)
    {
        $phone = self::normalize_phone($phone);
        $country_codes = [
            '+1' => 'US',
            '+44' => 'GB',
            '+33' => 'FR',
            '+49' => 'DE',
            '+39' => 'IT',
            '+34' => 'ES',
            '+351' => 'PT',
            '+31' => 'NL',
            '+32' => 'BE',
            '+41' => 'CH',
            '+43' => 'AT',
            '+45' => 'DK',
            '+46' => 'SE',
            '+47' => 'NO',
            '+358' => 'FI',
            '+353' => 'IE',
            '+48' => 'PL',
            '+420' => 'CZ',
            '+36' => 'HU',
            '+30' => 'GR',
            '+972' => 'IL',
            '+971' => 'AE',
            '+966' => 'SA',
            '+91' => 'IN',
            '+86' => 'CN',
            '+81' => 'JP',
            '+82' => 'KR',
            '+61' => 'AU',
            '+64' => 'NZ',
            '+55' => 'BR',
            '+52' => 'MX',
            '+54' => 'AR',
            '+27' => 'ZA'
        ];
        
        foreach ($country_codes as $code => $country) {
            if (strpos($phone, $code) === 0) {
                return $country;
            }
        }
        
        return null;
    }
    
    public static function normalize_domain($domain)
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        $domain = rtrim($domain, '/');
        return $domain;
    }
    
    public static function verify_code($license_key, $domain, $phone, $code)
    {
        $api_url = self::API_BASE_URL . '/verification/check';
        
        $normalized_phone = self::normalize_phone($phone);
        $country = self::get_country_from_phone($normalized_phone);
        
        if (!$country) {
            return ['valid' => false, 'error' => 'Country not supported'];
        }
        
        $data = json_encode([
            'license_key' => $license_key,
            'domain' => self::normalize_domain($domain),
            'phone' => $normalized_phone,
            'country' => $country,
            'code' => $code
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            return json_decode($response, true);
        }
        
        return ['valid' => false, 'error' => 'API request failed'];
    }
    
    public static function get_verification_logs($license_key)
    {
        $api_url = self::API_BASE_URL . '/licenses/' . urlencode($license_key) . '/logs';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            return ['success' => false, 'error' => 'Connection error: ' . $curl_error];
        }
        
        if ($http_code !== 200) {
            $error_result = json_decode($response, true);
            if ($error_result && isset($error_result['error'])) {
                return $error_result;
            }
            return ['success' => false, 'error' => 'API error: ' . $http_code];
        }
        
        if ($response) {
            $result = json_decode($response, true);
            if (is_array($result)) {
                return $result;
            }
        }
        
        return ['success' => false, 'error' => 'Invalid API response'];
    }
}
