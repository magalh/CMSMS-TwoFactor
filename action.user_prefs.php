<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
if( !$this->CheckPermission(TwoFactor::USE_PERM) ) return;

// Always use the currently logged-in user, never trust session variables
$uid = get_userid(false);
if (!$uid) {
    echo '<p class="error">You must be logged in to access this page.</p>';
    return;
}

echo $this->StartTabHeaders();
echo $this->SetTabHeader('methods', $this->Lang('tab_methods'));
echo $this->SetTabHeader('trusted_devices', $this->Lang('tab_trusted_devices'));
echo $this->EndTabHeaders();

echo $this->StartTabContent();

echo $this->StartTab('methods', $params);
include(__DIR__ . '/function.user_methods.php');
echo $this->EndTab();

echo $this->StartTab('trusted_devices', $params);
include(__DIR__ . '/function.user_trusted_devices.php');
echo $this->EndTab();

echo $this->EndTabContent();
