<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;

$uid = get_userid(false);

// Handle set primary
if (isset($params['set_primary'])) {
    TwoFactorCore::set_primary_provider($uid, $params['set_primary']);
    $this->SetMessage($this->Lang('primary_updated'));
    $this->RedirectToAdminTab('methods', '', 'user_prefs');
    return;
}

$providers = TwoFactorCore::get_providers();
$enabled_providers = TwoFactorUserMeta::get_enabled_providers($uid);
$primary_provider = TwoFactorUserMeta::get_primary_provider($uid);
$smscredit_enabled = get_site_preference('twofactor_smscredit_enabled', '0');
$sms_action = ($smscredit_enabled == '1') ? 'setup_sms_credit_enabled' : 'setup_sms';

$smarty = cmsms()->GetSmarty();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource('user_methods.tpl'), null, null, $smarty);
$tpl->assign('providers', $providers);
$tpl->assign('enabled_providers', $enabled_providers);
$tpl->assign('primary_provider', $primary_provider);
$tpl->assign('user_id', $uid);
$tpl->assign('actionid', $id);
$tpl->assign('smscredit_enabled', $smscredit_enabled);
$tpl->assign('sms_action', $sms_action);
$tpl->assign('mod', $this);
$tpl->display();
