<?php
# See doc/LICENSE.txt for full license information.
class TwoFactorCore
{
    private static $providers = [];

    public static function register_providers()
    {
        self::$providers = [
            'TwoFactorProviderTOTP' => dirname(__FILE__) . '/class.TwoFactorProviderTOTP.php',
            'TwoFactorProviderEmail' => dirname(__FILE__) . '/class.TwoFactorProviderEmail.php',
            'TwoFactorProviderSMS' => dirname(__FILE__) . '/class.TwoFactorProviderSMS.php',
            'TwoFactorProviderBackupCodes' => dirname(__FILE__) . '/class.TwoFactorProviderBackupCodes.php',
        ];
        
        foreach (self::$providers as $class => $path) {
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    public static function get_providers()
    {
        if (empty(self::$providers)) {
            self::register_providers();
        }

        $instances = [];
        foreach (self::$providers as $class => $path) {
            if (class_exists($class)) {
                $instances[$class] = call_user_func([$class, 'get_instance']);
            }
        }
        return $instances;
    }

    public static function get_available_providers_for_user($user_id)
    {
        $all_providers = self::get_providers();
        $enabled = TwoFactorUserMeta::get_enabled_providers($user_id);
        $available = [];

        foreach ($all_providers as $key => $provider) {
            if (in_array($key, $enabled) && $provider->is_available_for_user($user_id)) {
                $available[$key] = $provider;
            }
        }

        return $available;
    }

    public static function get_primary_provider_for_user($user_id)
    {
        $primary_key = TwoFactorUserMeta::get_primary_provider($user_id);
        $available = self::get_available_providers_for_user($user_id);

        if ($primary_key && isset($available[$primary_key])) {
            return $available[$primary_key];
        }

        return !empty($available) ? reset($available) : null;
    }

    public static function is_user_using_two_factor($user_id)
    {
        $primary = TwoFactorUserMeta::get_primary_provider($user_id);
        if ($primary === 'disabled') {
            return false;
        }
        return self::get_primary_provider_for_user($user_id) !== null;
    }

    public static function enable_provider_for_user($user_id, $provider_key)
    {
        $enabled = TwoFactorUserMeta::get_enabled_providers($user_id);
        if (!in_array($provider_key, $enabled)) {
            $enabled[] = $provider_key;
            return TwoFactorUserMeta::set_enabled_providers($user_id, $enabled);
        }
        return true;
    }

    public static function disable_provider_for_user($user_id, $provider_key)
    {
        $enabled = TwoFactorUserMeta::get_enabled_providers($user_id);
        $enabled = array_diff($enabled, [$provider_key]);
        return TwoFactorUserMeta::set_enabled_providers($user_id, array_values($enabled));
    }

    public static function set_primary_provider($user_id, $provider_key)
    {
        return TwoFactorUserMeta::set_primary_provider($user_id, $provider_key);
    }

    public static function get_template($params, $key, $typename)
    {
        if($key)
        {
        $tpl = \xt_param::get_string($params, $key);
        if($tpl) { return $tpl; }
        }
        
        $tpl = \CmsLayoutTemplate::load_dflt_by_type($typename);
        
        if($tpl) { return $tpl->get_name(); }
        
        \audit('', 'TwoFactor', 'No default template of type ' . $typename . ' found');
        
        return '';
    }
}
