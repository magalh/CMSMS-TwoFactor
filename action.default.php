<?php
# See LICENSE for full license information.
if( !defined('CMS_VERSION') ) exit;

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$config = cms_utils::get_config();

// Handle query parameters
if (isset($params['subaction'])) {
    if ($params['subaction'] === 'resend') {
        $params['resend'] = 1;
    } elseif ($params['subaction'] === 'primary') {
        $params['provider'] = '';
    } elseif ($params['subaction'] === 'backup-codes') {
        $params['provider'] = 'TwoFactorProviderBackupCodes';
    } elseif ($params['subaction'] === 'totp') {
        $params['provider'] = 'TwoFactorProviderTOTP';
    } elseif ($params['subaction'] === 'email') {
        $params['provider'] = 'TwoFactorProviderEmail';
    } elseif ($params['subaction'] === 'sms') {
        $params['provider'] = 'TwoFactorProviderSMS';
    } elseif ($params['subaction'] === 'passkey') {
        $params['provider'] = 'TwoFactorProviderPasskey';
    } elseif ($params['subaction'] === 'security-key') {
        $params['provider'] = 'TwoFactorProviderSecurityKey';
    } elseif (strpos($params['subaction'], 'TwoFactorProvider') === 0) {
        $params['provider'] = $params['subaction'];
    }
}

// Determine which login flow we're in
$new_flow = TwoFactor::hasNewLoginFlow() && isset($_SESSION['cms_pending_auth_userid']);

if ($new_flow) {
    // New core: pending auth timeout check (5 minutes)
    $pending_time = $_SESSION['cms_pending_auth_time'] ?? null;
    if (!$pending_time || $pending_time < (time() - 300)) {
        unset($_SESSION['cms_pending_auth_userid'], $_SESSION['cms_pending_effective_userid'], $_SESSION['cms_pending_auth_time']);
        redirect($config['admin_url'] . '/login.php');
        exit;
    }
    $uid = (int) $_SESSION['cms_pending_auth_userid'];
} elseif (isset($_SESSION['twofactor_user_id'])) {
    // Old core
    $uid = $_SESSION['twofactor_user_id'];
} else {
    redirect($config['admin_url'] . '/login.php');
    exit;
}

// Load effective user for new core
$effective_user = null;
if ($new_flow && !empty($_SESSION['cms_pending_effective_userid'])) {
    $effective_user = UserOperations::get_instance()->LoadUserByID((int)$_SESSION['cms_pending_effective_userid']);
}

// Check if device is trusted
if (TwoFactor::IsProActive() && class_exists('TwoFactorTrustedDevice') && TwoFactorTrustedDevice::is_trusted($uid)) {
    $user = UserOperations::get_instance()->LoadUserByID($uid);
    if ($user) {
        $login_ops = CMSMS\LoginOperations::get_instance();
        $rememberme = $_SESSION['twofactor_rememberme'] ?? 0;

        if ($new_flow) {
            $key = $login_ops->finalize_authentication($user, $effective_user);
            unset($_SESSION['cms_pending_auth_userid'], $_SESSION['cms_pending_effective_userid'], $_SESSION['cms_pending_auth_time']);
        } else {
            $key = $login_ops->save_authentication($user);
            unset($_SESSION['twofactor_user_id']);
        }
        
        if ($rememberme) {
            setcookie(CMS_USER_KEY, $key, time() + 2592000);
        }
        
        unset($_SESSION['twofactor_rememberme']);
        
        audit($uid, 'Admin Username: ' . $user->username, 'Logged In (2FA - Trusted Device)');
        
        redirect($config['admin_url'] . '/index.php');
        exit;
    }
}

// Handle provider switch
if (isset($params['provider'])) {
    if ($params['provider'] !== '') {
        // Normalize provider name (fix old singular form and aliases)
        $provider_name = $params['provider'];
        if ($provider_name === 'TwoFactorProviderBackupCode') {
            $provider_name = 'TwoFactorProviderBackupCodes';
        } elseif ($provider_name === 'backup-codes') {
            $provider_name = 'TwoFactorProviderBackupCodes';
        } elseif ($provider_name === 'totp') {
            $provider_name = 'TwoFactorProviderTOTP';
        } elseif ($provider_name === 'email') {
            $provider_name = 'TwoFactorProviderEmail';
        } elseif ($provider_name === 'sms') {
            $provider_name = 'TwoFactorProviderSMS';
        } elseif ($provider_name === 'passkey') {
            $provider_name = 'TwoFactorProviderPasskey';
        } elseif ($provider_name === 'security-key') {
            $provider_name = 'TwoFactorProviderSecurityKey';
        }
        $_SESSION['twofactor_override_provider'] = $provider_name;
    } else {
        unset($_SESSION['twofactor_override_provider']);
    }
    unset($_SESSION['twofactor_email_sent']);
    unset($_SESSION['twofactor_sms_sent']);
    $url = $config['root_url'] . '/twofactor/verify?_=' . time();
    redirect($url);
    exit;
}

// Handle resend request
if (isset($params['resend'])) {

    unset($_SESSION['twofactor_email_sent']);
    unset($_SESSION['twofactor_sms_sent']);
    $_SESSION['twofactor_message'] = $this->Lang('code_resent');
    $url = $config['root_url'] . '/twofactor/verify?_=' . time();
    redirect($url);
    exit;
}

// Get provider (check for override first)
if (isset($_SESSION['twofactor_override_provider'])) {
    $provider_key = $_SESSION['twofactor_override_provider'];
    $available = TwoFactorCore::get_available_providers_for_user($uid);
    $provider = $available[$provider_key] ?? null;
    if (!$provider) {
        unset($_SESSION['twofactor_override_provider']);
        $provider = TwoFactorCore::get_primary_provider_for_user($uid);
    }
} else {
    $provider = TwoFactorCore::get_primary_provider_for_user($uid);
}

if (!$provider) {
    if ($new_flow) {
        unset($_SESSION['cms_pending_auth_userid'], $_SESSION['cms_pending_effective_userid'], $_SESSION['cms_pending_auth_time']);
    } else {
        unset($_SESSION['twofactor_user_id']);
    }
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

                if ($new_flow) {
                    $key = $login_ops->finalize_authentication($user, $effective_user);
                    unset($_SESSION['cms_pending_auth_userid'], $_SESSION['cms_pending_effective_userid'], $_SESSION['cms_pending_auth_time']);
                } else {
                    $key = $login_ops->save_authentication($user);
                    session_regenerate_id(true);
                    unset($_SESSION['twofactor_user_id']);
                }
                
                if ($rememberme) {
                    setcookie(CMS_USER_KEY, $key, time() + 2592000);
                }
                
                unset($_SESSION['twofactor_rememberme']);
                unset($_SESSION['twofactor_email_sent']);
                unset($_SESSION['twofactor_sms_sent']);
                unset($_SESSION['twofactor_override_provider']);
                unset($_SESSION['twofactor_error']);
                unset($_SESSION['twofactor_webauthn_challenge']);
                
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
} elseif (strpos($provider_class, 'Passkey') !== false) {
    $template = 'verify_passkey.tpl';
    $webauthn_options = $provider->get_authentication_options($uid);
} elseif (strpos($provider_class, 'SecurityKey') !== false) {
    $template = 'verify_security_key.tpl';
    $webauthn_options = $provider->get_authentication_options($uid);
} elseif (strpos($provider_class, 'BackupCodes') !== false) {
    $template = 'verify_backup_codes.tpl';
}

$smarty = cmsms()->GetSmarty();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource($template), null, null, $smarty);
$tpl->assign('mod', $this);
$tpl->assign('mod_url', $this->GetModuleURLPath());
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
if (isset($webauthn_options)) {
    $tpl->assign('webauthn_options_json', json_encode($webauthn_options));
}

// Build alternative methods list (exclude current provider and backup codes)
$available = TwoFactorCore::get_available_providers_for_user($uid);
$alt_methods = [];
$slug_map = [
    'TwoFactorProviderTOTP'        => 'totp',
    'TwoFactorProviderEmail'       => 'email',
    'TwoFactorProviderSMS'         => 'sms',
    'TwoFactorProviderPasskey'     => 'passkey',
    'TwoFactorProviderSecurityKey' => 'security-key',
];
$label_map = [
    'TwoFactorProviderTOTP'        => 'provider_totp',
    'TwoFactorProviderEmail'       => 'provider_email',
    'TwoFactorProviderSMS'         => 'provider_sms',
    'TwoFactorProviderPasskey'     => 'provider_passkey',
    'TwoFactorProviderSecurityKey' => 'provider_security_key',
];
foreach ($available as $key => $p) {
    if ($key === $provider_class) continue;
    if (strpos($key, 'BackupCodes') !== false) continue;
    if (isset($slug_map[$key])) {
        $alt_methods[] = [
            'slug'  => $slug_map[$key],
            'label' => $this->Lang($label_map[$key]),
        ];
    }
}
$tpl->assign('alt_methods', $alt_methods);

$tpl->display();