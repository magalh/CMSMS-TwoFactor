<?php
# See modules/TwoFactor/doc/LICENSE.txt for full license information.
namespace CMSMS;

$CMS_ADMIN_PAGE=1;
$CMS_LOGIN_PAGE=1;

require_once("../lib/include.php");
$gCms = \CmsApp::get_instance();

$login_ops = \CMSMS\LoginOperations::get_instance();
$login_ops->deauthenticate();

// Check if we have stored 2FA data
if (!isset($_SESSION['twofactor_user_id'])) {
    $config = \cms_utils::get_config();
    redirect($config['admin_url'] . '/login.php');
    exit;
}

$uid = $_SESSION['twofactor_user_id'];
$mod = \cms_utils::get_module('TwoFactor');

// Check if device is trusted
if (\TwoFactorTrustedDevice::is_trusted($uid)) {
    $user = \UserOperations::get_instance()->LoadUserByID($uid);
    if ($user) {
        $rememberme = $_SESSION['twofactor_rememberme'] ?? 0;
        $key = $login_ops->save_authentication($user);
        
        if ($rememberme) {
            setcookie(CMS_USER_KEY, $key, time() + 2592000);
        }
        
        unset($_SESSION['twofactor_user_id']);
        unset($_SESSION['twofactor_rememberme']);
        
        audit($uid, 'Admin Username: ' . $user->username, 'Logged In (2FA - Trusted Device)');
        
        $config = \cms_utils::get_config();
        redirect($config['admin_url'] . '/index.php');
        exit;
    }
}

// Handle provider switch
if (isset($_GET['provider'])) {
    if ($_GET['provider']) {
        $_SESSION['twofactor_override_provider'] = $_GET['provider'];
    } else {
        unset($_SESSION['twofactor_override_provider']);
    }
    unset($_SESSION['twofactor_email_sent']);
    unset($_SESSION['twofactor_sms_sent']);
}

// Handle resend request
if (isset($_GET['resend'])) {
    unset($_SESSION['twofactor_email_sent']);
    unset($_SESSION['twofactor_sms_sent']);
    $config = \cms_utils::get_config();
    redirect($config['admin_url'] . '/twofactor.php');
    exit;
}

// Get provider (check for override first)
if (isset($_SESSION['twofactor_override_provider'])) {
    $provider_key = $_SESSION['twofactor_override_provider'];
    $available = \TwoFactorCore::get_available_providers_for_user($uid);
    $provider = $available[$provider_key] ?? null;
} else {
    $provider = \TwoFactorCore::get_primary_provider_for_user($uid);
}

if (!$provider) {
    unset($_SESSION['twofactor_user_id']);
    $config = \cms_utils::get_config();
    redirect($config['admin_url'] . '/login.php');
    exit;
}

$error = '';
$locked_seconds = false;
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Check for error from previous redirect
if (isset($_SESSION['twofactor_error'])) {
    $error = $_SESSION['twofactor_error'];
    unset($_SESSION['twofactor_error']);
}

// Check rate limiting (Pro feature)
if (\TwoFactor::IsProEnabled()) {
    $locked_seconds = \TwoFactorRateLimiter::check_rate_limit($uid, $ip_address);
    if ($locked_seconds !== false) {
        $error = sprintf($mod->Lang('account_locked'), ceil($locked_seconds / 60));
    }
}

if (isset($_POST['submit']) && $locked_seconds === false) {
    $result = $provider->validate_authentication($uid);
    
    if ($result) {
        // Reset failed attempts on success
        if (\TwoFactor::IsProEnabled()) {
            \TwoFactorRateLimiter::reset_attempts($uid, $ip_address);
        }
        
        // Trust device if requested
        if (isset($_POST['trust_device']) && $_POST['trust_device'] == '1') {
            \TwoFactorTrustedDevice::trust_device($uid);
        }
        
        $user = \UserOperations::get_instance()->LoadUserByID($uid);
        if ($user) {
            $rememberme = $_SESSION['twofactor_rememberme'] ?? 0;
            $key = $login_ops->save_authentication($user);
            
            if ($rememberme) {
                setcookie(CMS_USER_KEY, $key, time() + 2592000);
            }
            
            unset($_SESSION['twofactor_user_id']);
            unset($_SESSION['twofactor_rememberme']);
            unset($_SESSION['twofactor_email_sent']);
            unset($_SESSION['twofactor_sms_sent']);
            unset($_SESSION['twofactor_override_provider']);
            unset($_SESSION['twofactor_error']);
            
            audit($uid, 'Admin Username: ' . $user->username, 'Logged In (2FA)');
            
            $config = \cms_utils::get_config();
            redirect($config['admin_url'] . '/index.php');
            exit;
        }
    }
    
    // Record failed attempt
    if (\TwoFactor::IsProEnabled()) {
        \TwoFactorRateLimiter::record_failed_attempt($uid, $ip_address);
        // Store error in session for after redirect
        $_SESSION['twofactor_error'] = $mod->Lang('invalid_code');
        // Redirect to refresh lockout status
        $config = \cms_utils::get_config();
        redirect($config['admin_url'] . '/twofactor.php');
        exit;
    }
    
    $error = $mod->Lang('invalid_code');
}

cms_admin_sendheaders();
header("Content-Language: " . \CmsNlsOperations::get_current_language());

$config = \cms_config::get_instance();
$smarty = \Smarty_CMS::get_instance();

$mod_path = $mod->GetModulePath();
$provider_class = get_class($provider);
$template = 'verify_totp.tpl'; // Default

// Check if backup codes are available
$backup_provider = \TwoFactorProviderBackupCodes::get_instance();
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

$smarty->template_dir = $mod_path . '/templates';
$smarty->assign('encoding', get_encoding());
$smarty->assign('config', $config);
$smarty->assign('error', $error);
$smarty->assign('has_backup_codes', $has_backup_codes);
$smarty->assign('using_backup', $using_backup);
$smarty->assign('locked_seconds', $locked_seconds);
$smarty->display($template);
