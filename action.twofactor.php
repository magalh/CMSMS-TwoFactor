<?php
if( !defined('CMS_VERSION') ) exit;

$config = cms_utils::get_config();

// Check if we have stored 2FA data
if (!isset($_SESSION['twofactor_user_id'])) {
    redirect($config['admin_url'] . '/login.php');
    exit;
}

$uid = $_SESSION['twofactor_user_id'];

// Check if device is trusted
if (TwoFactor::IsProActive() && class_exists('TwoFactorTrustedDevice') && TwoFactorTrustedDevice::is_trusted($uid)) {
    $user = UserOperations::get_instance()->LoadUserByID($uid);
    if ($user) {
        $login_ops = CMSMS\LoginOperations::get_instance();
        $rememberme = $_SESSION['twofactor_rememberme'] ?? 0;
        $key = $login_ops->save_authentication($user);
        
        if ($rememberme) {
            setcookie(CMS_USER_KEY, $key, time() + 2592000);
        }
        
        unset($_SESSION['twofactor_user_id']);
        unset($_SESSION['twofactor_rememberme']);
        
        audit($uid, 'Admin Username: ' . $user->username, 'Logged In (2FA - Trusted Device)');
        
        redirect($config['admin_url'] . '/index.php');
        exit;
    }
}

// Handle provider switch
if (isset($params['provider'])) {
    if ($params['provider']) {
        // Normalize provider name (fix old singular form)
        $provider_name = $params['provider'];
        if ($provider_name === 'TwoFactorProviderBackupCode') {
            $provider_name = 'TwoFactorProviderBackupCodes';
        }
        $_SESSION['twofactor_override_provider'] = $provider_name;
    } else {
        unset($_SESSION['twofactor_override_provider']);
    }
    unset($_SESSION['twofactor_email_sent']);
    unset($_SESSION['twofactor_sms_sent']);
    redirect($config['root_url'] . '/index.php?mact=TwoFactor,cntnt01,twofactor,0&cntnt01showtemplate=false');
    exit;
}

// Handle resend request
if (isset($params['resend'])) {
    unset($_SESSION['twofactor_email_sent']);
    unset($_SESSION['twofactor_sms_sent']);
    $_SESSION['twofactor_message'] = $this->Lang('code_resent');
    redirect($config['root_url'] . '/index.php?mact=TwoFactor,cntnt01,twofactor,0&cntnt01showtemplate=false');
    exit;
}

// Get provider (check for override first)
if (isset($_SESSION['twofactor_override_provider'])) {
    $provider_key = $_SESSION['twofactor_override_provider'];
    $available = TwoFactorCore::get_available_providers_for_user($uid);
    $provider = $available[$provider_key] ?? null;
    error_log("TwoFactor: Provider override requested: $provider_key, provider found: " . ($provider ? get_class($provider) : 'NONE'));
    if (!$provider) {
        unset($_SESSION['twofactor_override_provider']);
        $provider = TwoFactorCore::get_primary_provider_for_user($uid);
    }
} else {
    $provider = TwoFactorCore::get_primary_provider_for_user($uid);
}

if (!$provider) {
    unset($_SESSION['twofactor_user_id']);
    redirect($config['admin_url'] . '/login.php');
    exit;
}

$error = '';
$message = '';
$locked_seconds = false;
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Check for message from previous redirect
if (isset($_SESSION['twofactor_message'])) {
    $message = $_SESSION['twofactor_message'];
    unset($_SESSION['twofactor_message']);
}

// Check for error from previous redirect
if (isset($_SESSION['twofactor_error'])) {
    $error = $_SESSION['twofactor_error'];
    unset($_SESSION['twofactor_error']);
}

// Check rate limiting (Pro feature)
if (TwoFactor::IsProActive()) {
    $locked_seconds = TwoFactorRateLimiter::check_rate_limit($uid, $ip_address);
    if ($locked_seconds !== false) {
        $error = sprintf($this->Lang('account_locked'), ceil($locked_seconds / 60));
    }
}

if (isset($params['submit']) && $locked_seconds === false) {
    
    // Validate CSRF token
    if (!\xt_utils::valid_form_csrf()) {
        $error = 'Invalid form submission';
    }
        
    if (!$error) {
        $result = $provider->validate_authentication($uid, $params);
    
        if ($result) {
            // Reset failed attempts on success
            if (TwoFactor::IsProActive()) {
                TwoFactorRateLimiter::reset_attempts($uid, $ip_address);
            }
            
            // Trust device if requested
            if (TwoFactor::IsProActive() && class_exists('TwoFactorTrustedDevice') && isset($params['trust_device']) && $params['trust_device'] == '1') {
                TwoFactorTrustedDevice::trust_device($uid);
            }
            
            $user = UserOperations::get_instance()->LoadUserByID($uid);
            if ($user) {
                $login_ops = CMSMS\LoginOperations::get_instance();
                $rememberme = $_SESSION['twofactor_rememberme'] ?? 0;
                $key = $login_ops->save_authentication($user);
                
                if ($rememberme) {
                    setcookie(CMS_USER_KEY, $key, time() + 2592000);
                }
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                unset($_SESSION['twofactor_user_id']);
                unset($_SESSION['twofactor_rememberme']);
                unset($_SESSION['twofactor_email_sent']);
                unset($_SESSION['twofactor_sms_sent']);
                unset($_SESSION['twofactor_override_provider']);
                unset($_SESSION['twofactor_error']);
                
                audit($uid, 'Admin Username: ' . $user->username, 'Logged In (2FA)');
                
                redirect($config['admin_url'] . '/index.php');
                exit;
            }
        } else {
            $error = $this->Lang('invalid_code');
            if (TwoFactor::IsProActive()) {
                TwoFactorRateLimiter::record_failed_attempt($uid, $ip_address);
            }
        }
    }
}

$provider_class = get_class($provider);
$template = 'verify_totp.tpl'; // Default

// Check if backup codes are available
$backup_provider = TwoFactorProviderBackupCodes::get_instance();
$has_backup_codes = $backup_provider->is_available_for_user($uid);
$using_backup = strpos($provider_class, 'BackupCodes') !== false;

// Select template based on provider type
if (strpos($provider_class, 'TOTP') !== false) {
    $template = 'verify_totp.tpl';
} elseif (strpos($provider_class, 'Email') !== false) {
    $template = 'verify_email.tpl';
    // Send email code if not already sent
    if (!isset($_SESSION['twofactor_email_sent'])) {
        $provider->generate_and_send_code($uid);
        $_SESSION['twofactor_email_sent'] = true;
    }
} elseif (strpos($provider_class, 'SMS') !== false) {
    $template = 'verify_sms.tpl';
    // Send SMS code if not already sent
    if (!isset($_SESSION['twofactor_sms_sent'])) {
        $provider->generate_and_send_code($uid);
        $_SESSION['twofactor_sms_sent'] = true;
    }
} elseif (strpos($provider_class, 'BackupCodes') !== false) {
    $template = 'verify_backup_codes.tpl';
}

$smarty = cmsms()->GetSmarty();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource($template), null, null, $smarty);
$tpl->assign('mod', $this);
$tpl->assign('actionid', $id);
$tpl->assign('config', $config);
$tpl->assign('encoding', get_encoding());
$tpl->assign('sitename', cms_utils::get_config()['sitename'] ?? 'CMS Made Simple');
$tpl->assign('error', $error);
$tpl->assign('message', $message);
$tpl->assign('has_backup_codes', $has_backup_codes);
$tpl->assign('using_backup', $using_backup);
$tpl->assign('locked_seconds', $locked_seconds);
$tpl->assign('is_pro_active', TwoFactor::IsProActive());
$tpl->display();