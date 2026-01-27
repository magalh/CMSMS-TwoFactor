<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::MANAGE_PERM)) return;

if (isset($params['cancel'])) {
    $this->RedirectToAdminTab();
    return;
}

$uid = get_userid();
$provider = TwoFactorProviderEmail::get_instance();

// Handle enable
if (isset($params['enable'])) {
    TwoFactorCore::enable_provider_for_user($uid, 'TwoFactorProviderEmail');
    $this->SetMessage($this->Lang('email_enabled'));
    $this->RedirectToAdminTab();
    return;
}

// Handle disable
if (isset($params['disable'])) {
    TwoFactorCore::disable_provider_for_user($uid, 'TwoFactorProviderEmail');
    $this->SetMessage($this->Lang('email_disabled'));
    $this->RedirectToAdminTab();
    return;
}

$user = UserOperations::get_instance()->LoadUserByID($uid);
$is_enabled = in_array('TwoFactorProviderEmail', TwoFactorUserMeta::get_enabled_providers($uid));

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_email.tpl'), null, null, $smarty);
$tpl->assign('user_email', $user->email ?? '');
$tpl->assign('is_enabled', $is_enabled);
$tpl->display();
