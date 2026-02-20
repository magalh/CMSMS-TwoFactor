<?php
if( !defined('CMS_VERSION') ) exit;

if (!$this->CheckPermission(TwoFactor::MANAGE_PERM) && !$this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) {
    return;
}

$is_pro = TwoFactor::IsProActive();
$pro = cms_utils::get_module('TwoFactorPro');
$current_tab = isset($params['__activetab']) ? $params['__activetab'] : ($is_pro ? 'pro_settings' : 'sms');

echo '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0px;">
  <h3 style="margin:0;">'.$this->Lang('two_factor_settings').'</h3>
  <a href="https://pixelsolutions.biz" target="_blank" rel="noopener noreferrer">
    <img src="https://pixelsolution.s3.eu-south-1.amazonaws.com/logos/LOGO_3_COLOR_300.png" alt="Pixel Solutions" style="height:40px;" />
  </a>
</div>';

if ($is_pro) {
    echo '<div class="information">';
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
