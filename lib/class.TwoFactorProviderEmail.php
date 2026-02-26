<?php
# See doc/LICENSE.txt for full license information.
# See doc/LICENSE.txt for full license information.
class TwoFactorProviderEmail extends TwoFactorProvider
{
    private static $instance;

    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_key()
    {
        return 'TwoFactorProviderEmail';
    }

    public function get_label()
    {
        $mod = cms_utils::get_module('TwoFactor');
        return $mod->Lang('provider_email');
    }

    public function is_available_for_user($user_id)
    {
        $user = UserOperations::get_instance()->LoadUserByID($user_id);
        return $user && !empty($user->email);
    }

    public function generate_and_send_code($user_id)
    {
        $code = sprintf('%06d', mt_rand(0, 999999));
        $expiry = time() + 1800; // 30 minutes (increased from 10)
        
        TwoFactorUserMeta::update($user_id, 'email_code', $code);
        TwoFactorUserMeta::update($user_id, 'email_code_expiry', $expiry);
        
        $user = UserOperations::get_instance()->LoadUserByID($user_id);
        $mod = cms_utils::get_module('TwoFactor');
        
        $thetemplate = TwoFactorCore::get_template([], '', 'TwoFactor::email_verification');
        if (!$thetemplate) {
            error_log('TwoFactorProviderEmail: Email verification template not found.');
            return false;
        }
        
        $smarty = cmsms()->GetSmarty();
        $tpl = $smarty->CreateTemplate($mod->GetTemplateResource($thetemplate), null, null, $smarty);
        $tpl->assign('code', $code);
        $tpl->assign('user', $user);
        $body = $tpl->fetch();
        
        $mailer = new cms_mailer();
        $mailer->AddAddress($user->email);
        $mailer->SetSubject($mod->Lang('email_subject'));
        $mailer->IsHTML(true);
        $mailer->SetBody($body);
        
        return $mailer->Send();
    }

    public function validate_authentication($user_id, $params = [])
    {
        $submitted_code = preg_replace('/\s+/', '', $params['authcode'] ?? '');
        $stored_code = TwoFactorUserMeta::get($user_id, 'email_code');
        $expiry = TwoFactorUserMeta::get($user_id, 'email_code_expiry');
        
        if (!$stored_code || !$expiry || time() > $expiry) {
            return false;
        }
        
        if (strval($submitted_code) === strval($stored_code)) {
            TwoFactorUserMeta::delete($user_id, 'email_code');
            TwoFactorUserMeta::delete($user_id, 'email_code_expiry');
            return true;
        }
        
        return false;
    }

    public function user_setup_form($user_id)
    {
        $user = UserOperations::get_instance()->LoadUserByID($user_id);
        $mod = cms_utils::get_module('TwoFactor');
        
        if (!$user || !$user->email) {
            return '<p>' . $mod->Lang('email_no_address') . '</p>';
        }
        
        return '<p>' . $mod->Lang('email_setup_info', $user->email) . '</p>';
    }

    public function authentication_page($user_id)
    {
        return '';
    }
}
