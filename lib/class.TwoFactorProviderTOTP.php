<?php
# See doc/LICENSE.txt for full license information.
class TwoFactorProviderTOTP extends TwoFactorProvider
{
    const SECRET_META_KEY = '_two_factor_totp_key';

    public function get_key()
    {
        return 'TwoFactorProviderTOTP';
    }

    public function get_label()
    {
        return 'Authenticator App';
    }

    public function get_alternative_label()
    {
        return 'Use your authenticator app (TOTP)';
    }

    public function is_available_for_user($user_id)
    {
        $key = $this->get_user_totp_key($user_id);
        return !empty($key);
    }

    public function authentication_page($user)
    {
        echo '<p class="pagetext">Enter the code from your authenticator app:</p>';
        echo '<p class="pageinput">';
        echo '<label for="authcode">Authentication Code:</label><br/>';
        echo '<input type="text" inputmode="numeric" name="authcode" id="authcode" 
              class="input" value="" size="20" pattern="[0-9 ]*" 
              placeholder="123 456" autocomplete="off" />';
        echo '</p>';
        echo '<script>setTimeout(function(){document.getElementById("authcode").focus();}, 200);</script>';
    }

    public function validate_authentication($user_id)
    {
        $code = $this->sanitize_code_from_request('authcode', 6);
        error_log('TwoFactor TOTP: Received code: ' . ($code ? $code : 'empty'));
        
        if (!$code) return false;

        $key = $this->get_user_totp_key($user_id);
        error_log('TwoFactor TOTP: Secret key exists: ' . ($key ? 'yes' : 'no'));
        
        if (!$key) return false;

        $tfa = new \RobThree\Auth\TwoFactorAuth();
        $result = $tfa->verifyCode($key, $code, 2);
        error_log('TwoFactor TOTP: Verification result: ' . ($result ? 'true' : 'false'));
        
        return $result;
    }

    public function get_user_totp_key($user_id)
    {
        return TwoFactorUserMeta::get($user_id, self::SECRET_META_KEY);
    }

    public function set_user_totp_key($user_id, $key)
    {
        return TwoFactorUserMeta::update($user_id, self::SECRET_META_KEY, $key);
    }

    public function delete_user_totp_key($user_id)
    {
        return TwoFactorUserMeta::delete($user_id, self::SECRET_META_KEY);
    }

    public function generate_key()
    {
        $tfa = new \RobThree\Auth\TwoFactorAuth();
        return $tfa->createSecret();
    }

    public function get_qr_code_url($username, $secret)
    {
        $sitename = get_site_preference('sitename', 'My Website');
        $tfa = new \RobThree\Auth\TwoFactorAuth($sitename);
        return $tfa->getQRCodeImageAsDataUri($username, $secret);
    }
}
