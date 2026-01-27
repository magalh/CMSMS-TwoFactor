<?php
# See doc/LICENSE.txt for full license information.
# See doc/LICENSE.txt for full license information.
class TwoFactorProviderBackupCodes extends TwoFactorProvider
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
        return 'TwoFactorProviderBackupCodes';
    }

    public function get_label()
    {
        $mod = cms_utils::get_module('TwoFactor');
        return $mod->Lang('provider_backup_codes');
    }

    public function is_available_for_user($user_id)
    {
        $codes = $this->get_codes($user_id);
        return !empty($codes);
    }

    public function generate_codes($count = 10)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtolower(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    public function get_codes($user_id)
    {
        $codes = TwoFactorUserMeta::get($user_id, 'backup_codes');
        return is_array($codes) ? $codes : [];
    }

    public function set_codes($user_id, $codes)
    {
        return TwoFactorUserMeta::update($user_id, 'backup_codes', $codes);
    }

    public function validate_authentication($user_id)
    {
        $submitted_code = strtolower(preg_replace('/\s+/', '', $_POST['authcode'] ?? ''));
        $codes = $this->get_codes($user_id);
        
        if (in_array($submitted_code, $codes)) {
            $codes = array_diff($codes, [$submitted_code]);
            $this->set_codes($user_id, array_values($codes));
            return true;
        }
        
        return false;
    }

    public function user_setup_form($user_id)
    {
        return '';
    }

    public function authentication_page($user_id)
    {
        return '';
    }
}
