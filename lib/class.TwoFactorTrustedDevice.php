<?php
# See doc/LICENSE.txt for full license information.

class TwoFactorTrustedDevice {
    
    private static function get_device_fingerprint() {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $accept_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $accept_enc = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        
        return hash('sha256', $ua . $accept . $accept_lang . $accept_enc);
    }
    
    private static function get_device_name() {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Detect browser
        if (preg_match('/Edge\/([0-9.]+)/', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/Edg\/([0-9.]+)/', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/([0-9.]+)/', $ua) && !preg_match('/Chrome/', $ua)) {
            $browser = 'Safari';
        } else {
            $browser = 'Unknown Browser';
        }
        
        // Detect OS
        if (preg_match('/Windows NT 10/', $ua)) {
            $os = 'Windows 10';
        } elseif (preg_match('/Windows NT 11/', $ua)) {
            $os = 'Windows 11';
        } elseif (preg_match('/Mac OS X/', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/iPhone/', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/Android/', $ua)) {
            $os = 'Android';
        } else {
            $os = 'Unknown OS';
        }
        
        return $browser . ' on ' . $os;
    }
    
    public static function is_trusted($user_id) {
        if (!isset($_COOKIE['twofactor_device'])) {
            return false;
        }
        
        $token = $_COOKIE['twofactor_device'];
        $fingerprint = self::get_device_fingerprint();
        
        $db = cmsms()->GetDb();
        $query = "SELECT expires_at FROM " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices 
                  WHERE user_id = ? AND device_token = ? AND device_fingerprint = ? AND expires_at > ?";
        $result = $db->GetOne($query, array($user_id, $token, $fingerprint, time()));
        
        return $result !== false;
    }
    
    public static function trust_device($user_id = null) {
        // Always use the actual logged-in user if available
        if ($user_id === null) {
            $user_id = get_userid(false);
        }
        
        // If still no user_id, we're in the 2FA flow, use session
        if (!$user_id && isset($_SESSION['twofactor_user_id'])) {
            $user_id = $_SESSION['twofactor_user_id'];
        }
        
        if (!$user_id) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $fingerprint = self::get_device_fingerprint();
        $device_name = self::get_device_name();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        $db = cmsms()->GetDb();
        $query = "INSERT INTO " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices 
                  (user_id, device_token, device_fingerprint, device_name, ip_address, created_at, expires_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $db->Execute($query, array($user_id, $token, $fingerprint, $device_name, $ip_address, time(), $expires));
        
        setcookie('twofactor_device', $token, $expires, '/', '', true, true);
        
        return true;
    }
    
    public static function revoke_device($user_id, $device_id) {
        $db = cmsms()->GetDb();
        $query = "DELETE FROM " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices 
                  WHERE id = ? AND user_id = ?";
        $db->Execute($query, array($device_id, $user_id));
    }
    
    public static function get_user_devices($user_id) {
        $db = cmsms()->GetDb();
        $query = "SELECT id, device_name, ip_address, created_at, expires_at FROM " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices 
                  WHERE user_id = ? AND expires_at > ? ORDER BY created_at DESC";
        return $db->GetArray($query, array($user_id, time()));
    }
    
    public static function cleanup_expired() {
        $db = cmsms()->GetDb();
        $query = "DELETE FROM " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices WHERE expires_at <= ?";
        $db->Execute($query, array(time()));
    }
}
