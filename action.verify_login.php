<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;

// First, logout the user immediately
$login_ops = \CMSMS\LoginOperations::get_instance();
$login_ops->deauthenticate();

// Check if we have stored 2FA data
if (!isset($_SESSION['twofactor_user_id'])) {
    error_log('TwoFactor verify_login: No 2FA session');
    $config = cms_utils::get_config();
    redirect($config['admin_url'] . '/login.php');
    exit;
}

$uid = $_SESSION['twofactor_user_id'];
$provider = TwoFactorCore::get_primary_provider_for_user($uid);

if (!$provider) {
    error_log('TwoFactor verify_login: No provider');
    unset($_SESSION['twofactor_user_id']);
    $config = cms_utils::get_config();
    redirect($config['admin_url'] . '/login.php');
    exit;
}

if (isset($params['submit'])) {
    error_log('TwoFactor verify_login: Form submitted, validating...');
    $result = $provider->validate_authentication($uid);
    error_log('TwoFactor verify_login: Validation result = ' . ($result ? 'TRUE' : 'FALSE'));
    
    if ($result) {
        // Valid code - log user back in
        $user = UserOperations::get_instance()->LoadUserByID($uid);
        if ($user) {
            $rememberme = $_SESSION['twofactor_rememberme'] ?? 0;
            
            // Log user in
            $key = $login_ops->save_authentication($user);
            
            if ($rememberme) {
                setcookie(CMS_USER_KEY, $key, time() + 2592000);
            }
            
            // Clean up 2FA session
            unset($_SESSION['twofactor_user_id']);
            unset($_SESSION['twofactor_rememberme']);
            
            audit($uid, 'Admin Username: ' . $user->username, 'Logged In (2FA)');
            
            $config = cms_utils::get_config();
            redirect($config['admin_url'] . '/index.php');
            exit;
        }
    }
    $error_msg = $this->Lang('invalid_code');
}

cms_admin_sendheaders();
header("Content-Language: " . \CmsNlsOperations::get_current_language());

$themeObject = \cms_utils::get_theme_object();
$vars = array('error'=>$error);
if( isset($warningLogin) ) $vars['warningLogin'] = $warningLogin;
if( isset($acceptLogin) ) $vars['acceptLogin'] = $acceptLogin;
if( isset($changepwhash) ) $vars['changepwhash'] = $changepwhash;

$config = cms_config::get_instance();
$smarty = Smarty_CMS::get_instance();

$smarty->template_dir = __DIR__ . '/templates';
global $error,$warningLogin,$acceptLogin,$changepwhash;

$smarty->assign('lang', get_site_preference('frontendlang'));
$_contents = $smarty->display('verify_totp.tpl');
return $_contents;
