<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('templates');

if (!$this->CheckPermission(TwoFactor::MANAGE_TEMPLATES_PERM)) return;

// Handle save
if (isset($params['save_templates'])) {
    if (TwoFactor::IsProEnabled()) {
        set_site_preference('twofactor_reset_email_subject', trim($params['reset_email_subject']));
        set_site_preference('twofactor_reset_email_body', $params['reset_email_body']);
        set_site_preference('twofactor_alert_email_subject', trim($params['alert_email_subject']));
        set_site_preference('twofactor_alert_email_body', $params['alert_email_body']);
        $this->SetMessage($this->Lang('settings_saved'));
    }
    $this->RedirectToAdminTab('templates');
    return;
}

$is_pro = TwoFactor::IsProEnabled();

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('admin_templates.tpl'), null, null, $smarty);

$tpl->assign('is_pro', $is_pro);

if ($is_pro) {

    $reset_email_subject = get_site_preference('twofactor_reset_email_subject', 'Security Alert: Password Reset Required');
    $reset_email_body = get_site_preference('twofactor_reset_email_body', 
        "Your account has been temporarily locked due to multiple failed 2FA attempts.<br><br>" .
        "For security reasons, please reset your password:<br>" .
        "<a href='{reset_url}'>{reset_url}</a><br><br>" .
        "If you did not attempt to log in, please contact your administrator immediately."
    );
    $alert_email_subject = get_site_preference('twofactor_alert_email_subject', 'Security Alert: Multiple Failed 2FA Attempts');
    $alert_email_body = get_site_preference('twofactor_alert_email_body',
        "Multiple failed 2FA attempts detected:<br><br>" .
        "<strong>User:</strong> {username}<br>" .
        "<strong>IP Address:</strong> {ip_address}<br>" .
        "<strong>Failed Attempts:</strong> {attempts}<br>" .
        "<strong>Time:</strong> {time}<br><br>" .
        "This could indicate a brute force attack."
    );
    
    $tpl->assign('reset_email_subject', $reset_email_subject);
    $tpl->assign('reset_email_body', $reset_email_body);
    $tpl->assign('alert_email_subject', $alert_email_subject);
    $tpl->assign('alert_email_body', $alert_email_body);
}

$tpl->display();
