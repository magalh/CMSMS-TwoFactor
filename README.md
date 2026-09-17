# TwoFactor

Two-factor authentication module for CMS Made Simple. Adds 2FA to the admin login flow with multiple provider support.

## Providers

- **TOTP** (Authenticator App) - Google Authenticator, Authy, 1Password, etc.
- **Email** - One-time codes sent via email
- **SMS** - One-time codes sent via SMS (requires Twilio or Managed Credits)
- **Passkey** - WebAuthn/FIDO2 biometric and device authentication
- **Backup Codes** - One-time recovery codes

## Requirements

- CMS Made Simple 2.2.23+
- PHP 8.1+
- CMSMSExt module >= 1.5.2
- HTTPS (required for Passkey/WebAuthn support)

## Third-Party Services

This module connects to external services for specific features. No data is sent unless the feature is explicitly configured and enabled by the site administrator.

### Pixel Solutions License API

- **Purpose:** Validates SMS credit license keys and checks remaining credits.
- **Data sent:** License key, site domain.
- **When:** Only when SMS Credits feature is enabled and configured.
- **Endpoint:** `https://api.pixelsolutions.biz`
- **Terms of Service:** https://pixelsolutions.biz/terms
- **Privacy Policy:** https://pixelsolutions.biz/privacy

### Twilio Verify API

- **Purpose:** Sends SMS verification codes to users during 2FA setup and login.
- **Data sent:** User phone number, verification code request.
- **When:** Only when Twilio SMS provider is enabled and configured with API credentials.
- **Endpoint:** `https://verify.twilio.com`
- **Terms of Service:** https://www.twilio.com/legal/tos
- **Privacy Policy:** https://www.twilio.com/legal/privacy

## License

See doc/LICENSE for full license information.
