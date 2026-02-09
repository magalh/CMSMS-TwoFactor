<?php
# See doc/LICENSE.txt for full license information.

class TwoFactorRateLimiter
{
    /**
     * Record a failed authentication attempt
     */
    public static function record_failed_attempt($user_id, $ip_address)
    {
        $db = cmsms()->GetDb();
        $table = CMS_DB_PREFIX . 'mod_twofactor_failed_attempts';
        
        // Check if record exists
        $sql = "SELECT * FROM $table WHERE user_id = ? AND ip_address = ?";
        $row = $db->GetRow($sql, [$user_id, $ip_address]);
        
        if ($row) {
            // Update existing record
            $attempts = $row['attempt_count'] + 1;
            $backoff = self::get_backoff_time($attempts);
            $locked_until = time() + $backoff;
            
            $sql = "UPDATE $table SET attempt_count = ?, last_attempt = ?, locked_until = ? 
                    WHERE user_id = ? AND ip_address = ?";
            $db->Execute($sql, [$attempts, time(), $locked_until, $user_id, $ip_address]);
        } else {
            // Create new record
            $backoff = self::get_backoff_time(1);
            $locked_until = time() + $backoff;
            
            $sql = "INSERT INTO $table (user_id, ip_address, attempt_count, first_attempt, last_attempt, locked_until) 
                    VALUES (?, ?, 1, ?, ?, ?)";
            $db->Execute($sql, [$user_id, $ip_address, time(), time(), $locked_until]);
        }
        
        // Check if password reset needed
        $max_attempts = (int)get_site_preference('twofactor_max_attempts_reset', 10);
        if ($row && $row['attempt_count'] + 1 >= $max_attempts) {
            self::trigger_password_reset($user_id);
        }
        
        // Check if admin notification needed
        $notify_threshold = 5;
        if ($row && $row['attempt_count'] + 1 >= $notify_threshold) {
            self::notify_admin_suspicious_activity($user_id, $ip_address, $row['attempt_count'] + 1);
        }
    }
    
    /**
     * Check if user/IP is rate limited
     * @return int|false Seconds until unlock, or false if not locked
     */
    public static function check_rate_limit($user_id, $ip_address)
    {
        if (!TwoFactor::IsProEnabled()) {
            return false;
        }
        
        if (!get_site_preference('twofactor_rate_limiting_enabled', '1')) {
            return false;
        }
        
        // Check if IP is blacklisted
        if (self::is_ip_blacklisted($ip_address)) {
            return 999999999; // Permanently blocked
        }
        
        $db = cmsms()->GetDb();
        $table = CMS_DB_PREFIX . 'mod_twofactor_failed_attempts';
        
        $sql = "SELECT * FROM $table WHERE user_id = ? AND ip_address = ?";
        $row = $db->GetRow($sql, [$user_id, $ip_address]);
        
        if (!$row) {
            return false;
        }
        
        // Allow first 3 attempts without lockout
        if ($row['attempt_count'] <= 3) {
            return false;
        }
        
        // Check if still locked based on locked_until timestamp
        if ($row['locked_until'] > time()) {
            return $row['locked_until'] - time();
        }
        
        // Lockout expired but don't reset - let them try again
        return false;
    }
    
    /**
     * Reset failed attempts on successful login
     */
    public static function reset_attempts($user_id, $ip_address)
    {
        $db = cmsms()->GetDb();
        $table = CMS_DB_PREFIX . 'mod_twofactor_failed_attempts';
        
        $sql = "DELETE FROM $table WHERE user_id = ? AND ip_address = ?";
        $db->Execute($sql, [$user_id, $ip_address]);
    }
    
    /**
     * Calculate exponential backoff time
     * @return int Seconds to lock
     */
    public static function get_backoff_time($attempts)
    {
        $backoff_map = [
            1 => 0,       // No lockout
            2 => 0,       // No lockout
            3 => 0,       // No lockout
            4 => 10,      // 10 seconds (testing)
            5 => 30,      // 30 seconds (testing)
        ];
        
        if ($attempts >= 5) {
            return 30; // 30 seconds for testing
        }
        
        return $backoff_map[$attempts] ?? 0;
    }
    
    /**
     * Trigger password reset for user
     */
    private static function trigger_password_reset($user_id)
    {
        $user = UserOperations::get_instance()->LoadUserByID($user_id);
        if (!$user || empty($user->email)) {
            return;
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 3600; // 1 hour
        
        // Store token
        $db = cmsms()->GetDb();
        $sql = "INSERT INTO " . CMS_DB_PREFIX . "mod_twofactor_usermeta (user_id, meta_key, meta_value) 
                VALUES (?, 'password_reset_token', ?)";
        $db->Execute($sql, [$user_id, json_encode(['token' => $token, 'expiry' => $expiry])]);
        
        // Send email
        $config = cms_config::get_instance();
        $reset_url = $config['admin_url'] . '/login.php?forgotpw=1';
        
        // Get customizable email content
        $subject = get_site_preference('twofactor_reset_email_subject', 'Security Alert: Password Reset Required');
        $body = get_site_preference('twofactor_reset_email_body', 
            "Your account has been temporarily locked due to multiple failed 2FA attempts.<br><br>" .
            "For security reasons, please reset your password:<br>" .
            "<a href='{reset_url}'>{reset_url}</a><br><br>" .
            "If you did not attempt to log in, please contact your administrator immediately."
        );
        
        // Replace placeholders
        $body = str_replace('{username}', $user->username, $body);
        $body = str_replace('{reset_url}', $reset_url, $body);
        
        $mailer = new cms_mailer();
        $mailer->AddAddress($user->email);
        $mailer->SetSubject($subject);
        $mailer->IsHTML(true);
        $mailer->SetBody($body);
        $mailer->Send();
        
        // Clear session and redirect to login
        unset($_SESSION['twofactor_user_id']);
        unset($_SESSION['twofactor_rememberme']);
        unset($_SESSION['twofactor_email_sent']);
        unset($_SESSION['twofactor_sms_sent']);
        unset($_SESSION['twofactor_override_provider']);
        
        redirect($config['admin_url'] . '/login.php');
        exit;
    }
    
    /**
     * Notify admin of suspicious activity
     */
    private static function notify_admin_suspicious_activity($user_id, $ip_address, $attempts)
    {
        if (!get_site_preference('twofactor_notify_admin', '1')) {
            return;
        }
        
        $user = UserOperations::get_instance()->LoadUserByID($user_id);
        if (!$user) {
            return;
        }
        
        // Get admin user (user ID 1)
        $admin = UserOperations::get_instance()->LoadUserByID(1);
        if (!$admin || empty($admin->email)) {
            error_log('TwoFactor: Admin user has no email for notifications');
            return;
        }
        
        $subject = 'Security Alert: Multiple Failed 2FA Attempts';
        $body = "Multiple failed 2FA attempts detected:<br><br>";
        $body .= "<strong>User:</strong> {$user->username}<br>";
        $body .= "<strong>IP Address:</strong> {$ip_address}<br>";
        $body .= "<strong>Failed Attempts:</strong> {$attempts}<br>";
        $body .= "<strong>Time:</strong> " . date('Y-m-d H:i:s') . "<br><br>";
        $body .= "This could indicate a brute force attack.";
        
        // Get customizable email content
        $subject = get_site_preference('twofactor_alert_email_subject', 'Security Alert: Multiple Failed 2FA Attempts');
        $body = get_site_preference('twofactor_alert_email_body',
            "Multiple failed 2FA attempts detected:<br><br>" .
            "<strong>User:</strong> {username}<br>" .
            "<strong>IP Address:</strong> {ip_address}<br>" .
            "<strong>Failed Attempts:</strong> {attempts}<br>" .
            "<strong>Time:</strong> {time}<br><br>" .
            "This could indicate a brute force attack."
        );
        
        // Replace placeholders
        $body = str_replace('{username}', $user->username, $body);
        $body = str_replace('{ip_address}', $ip_address, $body);
        $body = str_replace('{attempts}', $attempts, $body);
        $body = str_replace('{time}', date('Y-m-d H:i:s'), $body);
        
        $mailer = new cms_mailer();
        $mailer->AddAddress($admin->email);
        $mailer->SetSubject($subject);
        $mailer->IsHTML(true);
        $mailer->SetBody($body);
        $mailer->Send();
    }
    
    /**
     * Check if IP is blacklisted
     */
    public static function is_ip_blacklisted($ip_address)
    {
        $blacklist = get_site_preference('twofactor_ip_blacklist', '');
        if (empty($blacklist)) {
            return false;
        }
        
        $ips = array_map('trim', explode(',', $blacklist));
        return in_array($ip_address, $ips);
    }
    
    /**
     * Add IP to blacklist
     */
    public static function blacklist_ip($ip_address)
    {
        $blacklist = get_site_preference('twofactor_ip_blacklist', '');
        $ips = array_filter(array_map('trim', explode(',', $blacklist)));
        
        if (!in_array($ip_address, $ips)) {
            $ips[] = $ip_address;
            set_site_preference('twofactor_ip_blacklist', implode(',', $ips));
        }
    }
    
    /**
     * Remove IP from blacklist
     */
    public static function unblacklist_ip($ip_address)
    {
        $blacklist = get_site_preference('twofactor_ip_blacklist', '');
        $ips = array_filter(array_map('trim', explode(',', $blacklist)));
        
        $ips = array_diff($ips, [$ip_address]);
        set_site_preference('twofactor_ip_blacklist', implode(',', $ips));
    }
}
