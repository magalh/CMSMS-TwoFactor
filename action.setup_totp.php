<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::USE_PERM)) return;

if (isset($params['cancel'])) {
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$uid = get_userid();
$provider = TwoFactorProviderTOTP::get_instance();

// Handle verification
if (isset($params['verify'])) {
    $key = $params['totp_key'] ?? '';
    $code = preg_replace('/\s+/', '', $params['authcode'] ?? '');
    
    $tfa = new \RobThree\Auth\TwoFactorAuth();
    if ($tfa->verifyCode($key, $code, 2)) {
        $provider->set_user_totp_key($uid, $key);
        TwoFactorCore::enable_provider_for_user($uid, 'TwoFactorProviderTOTP');
        
        $this->SetMessage($this->Lang('totp_enabled'));
        $this->RedirectToAdminTab('','','user_prefs');
        return;
    } else {
        $error = $this->Lang('invalid_code');
    }
}

// Handle reset
if (isset($params['reset'])) {
    $provider->delete_user_totp_key($uid);
    TwoFactorCore::disable_provider_for_user($uid, 'TwoFactorProviderTOTP');
    $this->SetMessage($this->Lang('totp_reset'));
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$key = $provider->get_user_totp_key($uid);
$is_configured = !empty($key);

if (!$is_configured) {
    $key = $provider->generate_key();
}

$username = get_username($uid);
$qr_code = $provider->get_qr_code_url($username, $key);

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_totp.tpl'), null, null, $smarty);
$tpl->assign('secret', $key);
$tpl->assign('qr_code', $qr_code);
$tpl->assign('is_configured', $is_configured);
$tpl->assign('error', $error ?? '');
$tpl->display();
