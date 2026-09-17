<?php
# See LICENSE for full license information.
if( !defined('CMS_VERSION') ) exit;

$db = $this->GetDb();
$oldver = $oldversion;
$newver = $this->GetVersion();

if( version_compare($oldver, '2.0.0') < 0 ) {
    $this->CreatePermission(TwoFactor::USE_PERM, 'Use TwoFactor');
    $this->CreatePermission(TwoFactor::MANAGE_SMS_PERM, 'Manage TwoFactor SMS');

    $this->RegisterEvents();

    $old_table = cms_db_prefix() . 'mod_twofactor_usermeta';
    $new_table = cms_db_prefix() . 'module_twofactor_usermeta';
    
    $query = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
    $old_exists = $db->GetOne($query, [$old_table]);
    $new_exists = $db->GetOne($query, [$new_table]);
    
    if( $old_exists && !$new_exists ) {
        $db->Execute("RENAME TABLE $old_table TO $new_table");
    }

    $uid = get_userid();

    $email_type = new CmsLayoutTemplateType();
    $email_type->set_originator($this->GetName());
    $email_type->set_name('email_verification');
    $email_type->set_dflt_flag(TRUE);
    $email_type->set_lang_callback('TwoFactor::page_type_lang_callback');
    $email_type->set_content_callback('TwoFactor::reset_page_type_defaults');
    $email_type->reset_content_to_factory();
    $email_type->save();

    $tpl = new CmsLayoutTemplate();
    $tpl->set_name('Email Verification');
    $tpl->set_owner($uid);
    $tpl->set_type($email_type);
    $tpl->set_content($email_type->get_dflt_contents());
    $tpl->set_type_dflt(TRUE);
    $tpl->save();
}

if( version_compare($oldver, '2.1.0') < 0 ) {
    // Remove legacy twofactor.php file if it exists
    $config = cms_config::get_instance();
    $dest = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
    if (is_file($dest)) {
        unlink($dest);
    }
}

if( version_compare($oldver, '3.0.0') < 0 ) {
    // Passkey/WebAuthn support - no schema changes needed for free tier
    // Free tier stores single passkey credential in usermeta table
    // Pro tier creates module_twofactor_webauthn_credentials table in its own upgrade
}

if( version_compare($oldver, '4.0.0') < 0 ) {
    // ---------------------------------------------------------------------
    // Login-flow migration: legacy Core::LoginPost -> Core::LoginVerified
    //
    // Versions prior to 4.0.0 intercepted the admin login by registering a
    // PERSISTED handler on the core "LoginPost" event (RegisterEvents() called
    // AddEventHandler('Core','LoginPost'), which writes a row into the
    // cms_event_handlers table). Because LoginPost fires AFTER a full session
    // is established, the module's DoEvent() then ran a deauthenticate() +
    // redirect cycle to force the 2FA challenge, a hacky "log in, then log
    // back out" workaround.
    //
    // CMSMS 2.2.23 (core PR #15) introduced a proper two-stage auth lifecycle
    // with the cancellable "Core::LoginVerified" hook, which fires BEFORE the
    // session is finalized. Version 4.0.0 uses that hook at runtime (see
    // InitializeAdmin()) and no longer registers any LoginPost handler.
    //
    // The old persisted LoginPost handler row is NOT removed automatically
    // when the module files are swapped, so it lingers in the database and
    // would keep invoking the legacy deauth path. Remove it here.
    //
    // RemoveEventHandler() is module-scoped: it deletes only THIS module's
    // handler row for the given (originator, event), leaving handlers that
    // belong to other modules (and the core event itself) untouched.
    // ---------------------------------------------------------------------
    $this->RemoveEventHandler('Core', 'LoginPost');

    // Remove the legacy standalone interceptor page if a stale copy survived.
    // (Pre-2.1.0 shipped admin/twofactor.php; normally handled in the < 2.1.0
    // step, repeated here defensively for sites that jumped straight to 4.0.0
    // from a very old release. No .htaccess or core login.php was ever patched
    // by this module, so there is nothing else on disk to revert.)
    $config = cms_config::get_instance();
    $legacy = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
    if( is_file($legacy) ) {
        @unlink($legacy);
    }

    audit('', $this->GetName(), 'Migrated login interception from Core::LoginPost to Core::LoginVerified (2.2.23+ flow)');
}