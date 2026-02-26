<?php
# See doc/LICENSE.txt for full license information.
abstract class TwoFactorProvider
{
    protected static $instances = [];

    public static function get_instance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }

    abstract public function get_key();
    abstract public function get_label();
    abstract public function authentication_page($user);
    abstract public function validate_authentication($user);
    abstract public function is_available_for_user($user);

    public function get_alternative_label()
    {
        return sprintf('Use %s', $this->get_label());
    }

    public function pre_process_authentication($user)
    {
        return false;
    }

    protected function get_code($length = 8, $chars = '1234567890')
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $code;
    }

    protected function sanitize_code_from_request($field, $length = 0)
    {
        if (empty($_REQUEST[$field])) return false;
        $code = preg_replace('/\s+/', '', $_REQUEST[$field]);
        if ($length && strlen($code) !== $length) return false;
        return $code;
    }
    
    protected function sanitize_code($value, $length = 0)
    {
        if (empty($value)) return false;
        $code = preg_replace('/\s+/', '', $value);
        if ($length && strlen($code) !== $length) return false;
        return $code;
    }
}
