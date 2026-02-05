<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
if( !$this->CheckPermission(TwoFactor::USE_PERM) ) return;

$uid = get_userid();

// Handle set primary
if (isset($params['set_primary'])) {
    TwoFactorCore::set_primary_provider($uid, $params['set_primary']);
    $this->SetMessage($this->Lang('primary_updated'));
    $this->RedirectToAdminTab('','','user_prefs');
    return;
}

$providers = TwoFactorCore::get_providers();
$enabled_providers = TwoFactorUserMeta::get_enabled_providers($uid);
$primary_provider = TwoFactorUserMeta::get_primary_provider($uid);

$smarty->assign('providers', $providers);
$smarty->assign('enabled_providers', $enabled_providers);
$smarty->assign('primary_provider', $primary_provider);
$smarty->assign('user_id', $uid);

echo $this->ProcessTemplate('user_prefs.tpl');
