<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;

$current = $oldversion;
$uid = max(1, get_userid(FALSE));

if( version_compare($oldversion,'1.1.2') < 0 ) {
        $this->CreatePermission(TwoFactor::USE_PERM, 'Use TwoFactor');     
        $email_type = new CmsLayoutTemplateType();
        $email_type->set_originator($this->GetName());
        $email_type->set_name('email_verification');
        $email_type->set_dflt_flag(TRUE);
        $email_type->set_lang_callback('TwoFactor::page_type_lang_callback');
        $email_type->set_content_callback('TwoFactor::reset_page_type_defaults');
        $email_type->reset_content_to_factory();
        $email_type->save();
        
}

if( version_compare($oldversion,'1.1.3') < 0 ) {

        $email_type = CmsLayoutTemplateType::load('TwoFactor::email_verification');
        if (!$email_type) {
            $email_type = new CmsLayoutTemplateType();
            $email_type->set_originator($this->GetName());
            $email_type->set_name('email_verification');
            $email_type->set_dflt_flag(TRUE);
            $email_type->set_lang_callback('TwoFactor::page_type_lang_callback');
            $email_type->set_content_callback('TwoFactor::reset_page_type_defaults');
            $email_type->reset_content_to_factory();
            $email_type->save();
        }
        
        $tpl = new CmsLayoutTemplate();
        $tpl->set_name($tpl::generate_unique_name('TwoFactor Email Verification'));
        $tpl->set_owner($uid);
        $tpl->set_type($email_type);
        $tpl->set_content($email_type->get_dflt_contents());
        $tpl->set_type_dflt(TRUE);
        $tpl->save();
}

if( version_compare($oldversion,'1.2.1') < 0 ) {
    // Add failed attempts table for rate limiting
    $db = $this->GetDb();
    $dict = NewDataDictionary($db);
    
    $flds = "
        id I KEY AUTO,
        user_id I NOTNULL,
        ip_address C(45) NOTNULL,
        attempt_count I DEFAULT 0,
        first_attempt I,
        last_attempt I,
        locked_until I
    ";
    $sqlarray = $dict->CreateTableSQL(CMS_DB_PREFIX.'mod_twofactor_failed_attempts', $flds);
    $dict->ExecuteSQLArray($sqlarray);
    
    $db->Execute('CREATE INDEX idx_user_ip ON '.CMS_DB_PREFIX.'mod_twofactor_failed_attempts (user_id, ip_address)');
    
    // Set default Pro settings
    set_site_preference('twofactor_rate_limiting_enabled', '1');
    set_site_preference('twofactor_max_attempts_lockout', '5');
    set_site_preference('twofactor_max_attempts_reset', '10');
    set_site_preference('twofactor_notify_admin', '1');
}
