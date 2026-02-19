<?php
if( !defined('CMS_VERSION') ) exit;

if (!$this->CheckPermission(TwoFactor::MANAGE_PERM) && !$this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) {
    return;
}

$is_pro = TwoFactor::IsProActive();
$pro = cms_utils::get_module('TwoFactorPro');
$current_tab = isset($params['__activetab']) ? $params['__activetab'] : ($is_pro ? 'pro_settings' : 'sms');

echo '<h3>TwoFactor Settings</h3>';

if ($is_pro) {
    echo '<div class="information" style="margin-bottom:20px;">';
    echo '<p><strong>✓ TwoFactor Pro Active !!BETA!!</strong> - Premium features enabled.</p>';
    echo '</div>';
}

echo $this->StartTabHeaders();
if ($pro && $is_pro) {
    echo $this->SetTabHeader('pro_settings', 'Settings');
    echo $this->SetTabHeader('user_management', 'User Management');
    echo $this->SetTabHeader('templates', 'Templates');
}
if ($this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) {
    echo $this->SetTabHeader('sms', 'SMS Settings');
    $smscredit_enabled = $this->GetPreference('twofactor_smscredit_enabled', 0);
    if ($smscredit_enabled) {
        echo $this->SetTabHeader('verify_logs', 'Verify Logs');
    }
}
if ($pro) {
    echo $this->SetTabHeader('license', 'Pro License');
}
if (!$is_pro && !$pro && $this->CheckPermission(TwoFactor::MANAGE_PERM)) {
    echo $this->SetTabHeader('upgrade', 'Upgrade to Pro');
}
$config = cms_config::get_instance();
if ($this->CheckPermission(TwoFactor::MANAGE_PERM) && isset($config['developer_mode'] ) && $config['developer_mode']  == '1') {
    echo $this->SetTabHeader('debug', 'Debug');
}
echo $this->EndTabHeaders();

echo $this->StartTabContent();

if ($pro && $is_pro) {
    echo $this->StartTab('pro_settings', $params);
    include($pro->GetModulePath() . '/function.admin_pro_settings.php');
    echo $this->EndTab();
    
    echo $this->StartTab('user_management', $params);
    include($pro->GetModulePath() . '/function.admin_user_management.php');
    echo $this->EndTab();
    
    echo $this->StartTab('templates', $params);
    include($pro->GetModulePath() . '/function.admin_templates.php');
    echo $this->EndTab();
}

if ($this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) {
    echo $this->StartTab('sms', $params);
    include(__DIR__ . '/function.admin_smssettings.php');
    echo $this->EndTab();
    
    $smscredit_enabled = $this->GetPreference('twofactor_smscredit_enabled', 0);
    if ($smscredit_enabled) {
        echo $this->StartTab('verify_logs', $params);
        include(__DIR__ . '/function.admin_verify_logs.php');
        echo $this->EndTab();
    }
}

if ($pro) {
    echo $this->StartTab('license', $params);
    include($pro->GetModulePath() . '/function.admin_license.php');
    echo $this->EndTab();
}

if (!$is_pro && !$pro && $this->CheckPermission(TwoFactor::MANAGE_PERM)) {
    echo $this->StartTab('upgrade', $params);
    include(__DIR__ . '/function.admin_upgrade.php');
    echo $this->EndTab();
}

$config = cms_config::get_instance();
if ($this->CheckPermission(TwoFactor::MANAGE_PERM) && isset($config['developer_mode'] ) && $config['developer_mode']  == '1') {
    echo $this->StartTab('debug', $params);
    include(__DIR__ . '/function.debug.php');
    echo $this->EndTab();
}

echo $this->EndTabContent();
