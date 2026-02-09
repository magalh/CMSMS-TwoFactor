<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
if( !$this->CheckPermission(TwoFactor::MANAGE_PERM) ) return;

$current_tab = isset($params['active_tab']) ? $params['active_tab'] : 'settings';

echo $this->StartTabHeaders();
echo $this->SetTabHeader('settings', $this->Lang('tab_settings'), $current_tab == 'settings');
echo $this->SetTabHeader('premium', $this->Lang('tab_premium'), $current_tab == 'premium');
echo $this->SetTabHeader('smscredit', $this->Lang('tab_smscredit'), $current_tab == 'smscredit');
echo $this->EndTabHeaders();

echo $this->StartTabContent();

echo $this->StartTab('settings', $params);
include(__DIR__ . '/function.admin_settings.php');
echo $this->EndTab();

echo $this->StartTab('premium', $params);
include(__DIR__ . '/function.admin_premium.php');
echo $this->EndTab();

echo $this->StartTab('smscredit', $params);
include(__DIR__ . '/function.admin_smssettings.php');
echo $this->EndTab();

echo $this->EndTabContent();
