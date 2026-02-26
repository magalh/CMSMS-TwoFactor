<?php
# See LICENSE for full license information.
if( !defined('CMS_VERSION') ) exit;
if( !$this->CheckPermission(TwoFactor::USE_PERM) ) return;

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('user_prefs.tpl'), null, null, $smarty);

// Check if 2FA is being enforced and user doesn't have it enabled
$pro = cms_utils::get_module('TwoFactorPro');
$enforce_2fa = $pro ? $pro->GetPreference('twofactorpro_enforce_all', 0) : 0;
$uid = get_userid(false);
$user_has_2fa = TwoFactorCore::is_user_using_two_factor($uid);

$tpl->assign('enforce_2fa', $enforce_2fa);
$tpl->assign('user_has_2fa', $user_has_2fa);
$tpl->display();

//TABS
$is_pro = TwoFactor::IsProActive();
$current_tab = isset($params['__activetab']) ? $params['__activetab'] : 'methods';

echo $this->StartTabHeaders();
echo $this->SetTabHeader('methods', $this->Lang('tab_methods'));
if ($is_pro) {
    echo $this->SetTabHeader('trusted_devices', $this->Lang('tab_trusted_devices'));
}
echo $this->EndTabHeaders();

echo $this->StartTabContent();

echo $this->StartTab('methods', $params);
include(__DIR__ . '/function.user_methods.php');
echo $this->EndTab();

if ($is_pro) {
    echo $this->StartTab('trusted_devices', $params);
    $pro = cms_utils::get_module('TwoFactorPro');
    if ($pro) {
        include($pro->GetModulePath() . '/function.user_trusted_devices.php');
    }
    echo $this->EndTab();
}

echo $this->EndTabContent();
