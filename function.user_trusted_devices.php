<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;

$uid = get_userid(false);

// Handle revoke device
if (isset($params['revoke_device'])) {
    $device_id = (int)$params['revoke_device'];
    
    // Verify device belongs to current user before revoking
    $db = $this->GetDb();
    $query = "SELECT user_id FROM " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices WHERE id = ?";
    $device_owner = $db->GetOne($query, array($device_id));
    
    if ($device_owner && $device_owner == $uid) {
        TwoFactorTrustedDevice::revoke_device($uid, $device_id);
        $this->SetMessage($this->Lang('device_revoked'));
    } else {
        $this->SetError($this->Lang('device_not_found'));
    }
    
    $this->RedirectToAdminTab('trusted_devices', '', 'user_prefs');
    return;
}

// Cleanup expired devices
TwoFactorTrustedDevice::cleanup_expired();

// Get user's trusted devices
$devices = TwoFactorTrustedDevice::get_user_devices($uid);

$smarty = cmsms()->GetSmarty();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource('user_trusted_devices.tpl'), null, null, $smarty);
$tpl->assign('devices', $devices);
$tpl->assign('actionid', $id);
$tpl->assign('mod', $this);
$tpl->display();
