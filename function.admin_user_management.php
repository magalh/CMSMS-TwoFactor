<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;

if (!$this->CheckPermission(TwoFactor::VIEW_USERS_PERM) && 
    !$this->CheckPermission(TwoFactor::MANAGE_USERS_PERM)) return;

// Handle disable 2FA for user
if (isset($params['disable_2fa_user'])) {
    $target_uid = (int)$params['disable_2fa_user'];
    
    // Delete all 2FA settings for this user
    $db = $this->GetDb();
    $query = "DELETE FROM " . CMS_DB_PREFIX . "mod_twofactor_usermeta WHERE user_id = ?";
    $db->Execute($query, array($target_uid));
    
    // Delete trusted devices
    $query = "DELETE FROM " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices WHERE user_id = ?";
    $db->Execute($query, array($target_uid));
    
    // Reset failed attempts
    $query = "DELETE FROM " . CMS_DB_PREFIX . "mod_twofactor_failed_attempts WHERE user_id = ?";
    $db->Execute($query, array($target_uid));
    
    $user = UserOperations::get_instance()->LoadUserByID($target_uid);
    $username = $user ? $user->username : 'User #' . $target_uid;
    
    $this->SetMessage(sprintf($this->Lang('user_2fa_disabled'), $username));
    $this->RedirectToAdminTab('user_management');
    return;
}

// Get all users with their 2FA status
$db = $this->GetDb();
$users_ops = UserOperations::get_instance();
$all_users = $users_ops->LoadUsers();
$current_user_id = get_userid(false);

$users_data = array();
foreach ($all_users as $user) {
    $user_id = $user->id;
    
    // Skip current user
    if ($user_id == $current_user_id) {
        continue;
    }
    
    // Check if user has 2FA enabled
    $enabled_providers = TwoFactorUserMeta::get_enabled_providers($user_id);
    $primary_provider = TwoFactorUserMeta::get_primary_provider($user_id);
    
    // Get provider names
    $provider_names = array();
    if (!empty($enabled_providers)) {
        $all_providers = TwoFactorCore::get_providers();
        foreach ($enabled_providers as $provider_key) {
            if (isset($all_providers[$provider_key])) {
                $provider_names[] = $all_providers[$provider_key]->get_label();
            }
        }
    }
    
    // Get failed attempts count
    $query = "SELECT attempt_count, locked_until FROM " . CMS_DB_PREFIX . "mod_twofactor_failed_attempts 
              WHERE user_id = ? ORDER BY last_attempt DESC LIMIT 1";
    $attempt_data = $db->GetRow($query, array($user_id));
    $failed_attempts = $attempt_data ? (int)$attempt_data['attempt_count'] : 0;
    $is_locked = $attempt_data && $attempt_data['locked_until'] > time();
    
    // Get trusted devices count
    $query = "SELECT COUNT(*) FROM " . CMS_DB_PREFIX . "mod_twofactor_trusted_devices 
              WHERE user_id = ? AND expires_at > ?";
    $trusted_devices = (int)$db->GetOne($query, array($user_id, time()));
    
    $users_data[] = array(
        'id' => $user_id,
        'username' => $user->username,
        'email' => $user->email,
        'has_2fa' => !empty($enabled_providers),
        'primary_provider' => $primary_provider,
        'enabled_providers' => $provider_names,
        'failed_attempts' => $failed_attempts,
        'is_locked' => $is_locked,
        'trusted_devices' => $trusted_devices
    );
}

$smarty = cmsms()->GetSmarty();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource('admin_user_management.tpl'), null, null, $smarty);
$tpl->assign('users', $users_data);
$tpl->assign('actionid', $id);
$tpl->assign('mod', $this);
$tpl->assign('can_manage', $this->CheckPermission(TwoFactor::MANAGE_USERS_PERM));
$tpl->display();
