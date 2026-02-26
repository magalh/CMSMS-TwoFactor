<?php
# See LICENSE for full license information.
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::USE_PERM)) return;

if (isset($params['cancel'])) {
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$uid = get_userid();
$provider = TwoFactorProviderBackupCodes::get_instance();

// Handle generate
if (isset($params['generate'])) {
    $codes = $provider->generate_codes(10);
    $provider->set_codes($uid, $codes);
    TwoFactorCore::enable_provider_for_user($uid, 'TwoFactorProviderBackupCodes');
    
    $tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_backup_codes.tpl'), null, null, $smarty);
    $tpl->assign('codes', $codes);
    $tpl->assign('new_codes', true);
    $tpl->display();
    return;
}

// Handle disable
if (isset($params['disable'])) {
    TwoFactorCore::disable_provider_for_user($uid, 'TwoFactorProviderBackupCodes');
    TwoFactorUserMeta::delete($uid, 'backup_codes');
    $this->SetMessage($this->Lang('backup_codes_disabled'));
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$codes = $provider->get_codes($uid);
$is_enabled = !empty($codes);

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_backup_codes.tpl'), null, null, $smarty);
$tpl->assign('codes', $codes);
$tpl->assign('is_enabled', $is_enabled);
$tpl->assign('new_codes', false);
$tpl->display();
