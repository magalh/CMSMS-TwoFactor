<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;

// Allow access if user has any admin permission
if (!$this->CheckPermission(TwoFactor::MANAGE_PERM) && 
    !$this->CheckPermission(TwoFactor::VIEW_USERS_PERM) &&
    !$this->CheckPermission(TwoFactor::MANAGE_USERS_PERM) &&
    !$this->CheckPermission(TwoFactor::MANAGE_TEMPLATES_PERM) &&
    !$this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) {
    return;
}

$current_tab = isset($params['active_tab']) ? $params['active_tab'] : 'settings';
$is_pro = TwoFactor::IsProEnabled();

echo '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">';
echo '<h3 style="margin:0;">' . $this->Lang('two_factor_settings') . '</h3>';
echo '<a href="https://pixelsolutions.biz" target="_blank" rel="noopener noreferrer">';
echo '<img src="https://pixelsolution.s3.eu-south-1.amazonaws.com/logos/LOGO_3_COLOR_300.png" alt="Pixel Solutions" style="height:40px;" />';
echo '</a>';
echo '</div>';

if ($is_pro) {
    echo '<div class="information" style="margin-bottom:20px;">';
    echo '<p><strong>✓ TwoFactor Pro Active</strong> - All premium features are enabled.</p>';
    echo '</div>';
}

echo $this->StartTabHeaders();
if ($is_pro && $this->CheckPermission(TwoFactor::MANAGE_PERM)) {
    echo $this->SetTabHeader('settings', $this->Lang('tab_settings'));
}
if ($this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) {
    echo $this->SetTabHeader('smscredit', $this->Lang('tab_smscredit'));
}
if ($is_pro && $this->CheckPermission(TwoFactor::MANAGE_TEMPLATES_PERM)) {
    echo $this->SetTabHeader('templates', $this->Lang('tab_templates'));
}
if ($is_pro && $this->CheckPermission(TwoFactor::VIEW_USERS_PERM)) {
    echo $this->SetTabHeader('user_management', $this->Lang('tab_user_management'));
}
if ($this->CheckPermission(TwoFactor::MANAGE_PERM)) {
    echo $this->SetTabHeader('premium', $this->Lang('tab_premium'));
}
echo $this->EndTabHeaders();

echo $this->StartTabContent();

if ($is_pro && $this->CheckPermission(TwoFactor::MANAGE_PERM)) {
    echo $this->StartTab('settings', $params);
    include(__DIR__ . '/function.admin_settings.php');
    echo $this->EndTab();
}

if ($this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) {
    echo $this->StartTab('smscredit', $params);
    include(__DIR__ . '/function.admin_smssettings.php');
    echo $this->EndTab();
}

if ($is_pro && $this->CheckPermission(TwoFactor::MANAGE_TEMPLATES_PERM)) {
    echo $this->StartTab('templates', $params);
    include(__DIR__ . '/function.admin_templates.php');
    echo $this->EndTab();
}

if ($is_pro && $this->CheckPermission(TwoFactor::VIEW_USERS_PERM)) {
    echo $this->StartTab('user_management', $params);
    include(__DIR__ . '/function.admin_user_management.php');
    echo $this->EndTab();
}

if ($this->CheckPermission(TwoFactor::MANAGE_PERM)) {
    echo $this->StartTab('premium', $params);
    include(__DIR__ . '/function.admin_premium.php');
    echo $this->EndTab();
}

echo $this->EndTabContent();
