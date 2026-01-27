<?php
# See doc/LICENSE.txt for full license information.
class TwoFactorUserMeta
{
    const ENABLED_PROVIDERS_KEY = '_two_factor_enabled_providers';
    const PRIMARY_PROVIDER_KEY = '_two_factor_primary_provider';

    public static function get($user_id, $key, $single = true)
    {
        $db = cms_utils::get_db();
        $sql = 'SELECT meta_value FROM '.CMS_DB_PREFIX.'mod_twofactor_usermeta 
                WHERE user_id = ? AND meta_key = ?';
        
        if ($single) {
            $value = $db->GetOne($sql, [$user_id, $key]);
            return $value ? unserialize($value) : null;
        }
        
        $rows = $db->GetCol($sql, [$user_id, $key]);
        return array_map('unserialize', $rows);
    }

    public static function update($user_id, $key, $value)
    {
        $db = cms_utils::get_db();
        $serialized = serialize($value);
        
        $exists = $db->GetOne(
            'SELECT id FROM '.CMS_DB_PREFIX.'mod_twofactor_usermeta 
             WHERE user_id = ? AND meta_key = ?',
            [$user_id, $key]
        );

        if ($exists) {
            return $db->Execute(
                'UPDATE '.CMS_DB_PREFIX.'mod_twofactor_usermeta 
                 SET meta_value = ? WHERE user_id = ? AND meta_key = ?',
                [$serialized, $user_id, $key]
            );
        }

        return $db->Execute(
            'INSERT INTO '.CMS_DB_PREFIX.'mod_twofactor_usermeta 
             (user_id, meta_key, meta_value) VALUES (?, ?, ?)',
            [$user_id, $key, $serialized]
        );
    }

    public static function delete($user_id, $key)
    {
        $db = cms_utils::get_db();
        return $db->Execute(
            'DELETE FROM '.CMS_DB_PREFIX.'mod_twofactor_usermeta 
             WHERE user_id = ? AND meta_key = ?',
            [$user_id, $key]
        );
    }

    public static function get_enabled_providers($user_id)
    {
        $providers = self::get($user_id, self::ENABLED_PROVIDERS_KEY);
        return is_array($providers) ? $providers : [];
    }

    public static function set_enabled_providers($user_id, array $providers)
    {
        return self::update($user_id, self::ENABLED_PROVIDERS_KEY, $providers);
    }

    public static function get_primary_provider($user_id)
    {
        return self::get($user_id, self::PRIMARY_PROVIDER_KEY);
    }

    public static function set_primary_provider($user_id, $provider_key)
    {
        return self::update($user_id, self::PRIMARY_PROVIDER_KEY, $provider_key);
    }
}
