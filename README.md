# TwoFactor - Two-Factor Authentication for CMS Made Simple

Secure your CMS Made Simple installation with two-factor authentication (2FA) by Pixel Solutions.

## Features

### Multiple Authentication Methods
- **TOTP (Time-based One-Time Password)** - Google Authenticator, Authy, Microsoft Authenticator, etc.
- **Email Verification** - Receive codes via email
- **SMS Verification** - Receive codes via SMS (Twilio or Managed SMS)
- **Backup Codes** - One-time emergency access codes

### Flexible Configuration
- Users can enable/disable methods individually
- Choose primary authentication method
- Multiple methods can be enabled as fallback options

### Security Features
- Intercepts login after username/password validation
- Session-based verification flow
- Event system for extensibility
- Secure code generation and validation

### SMS Options
**Option 1: Managed SMS Credits**
- Purchase SMS credits from Pixel Solutions
- Simple setup - just enter your product key
- Use credits across all your sites
- No API configuration needed

**Option 2: Your Own Twilio Account**
- Use your existing Twilio account
- Full control over SMS delivery
- Configure API credentials in settings

## Quick Start

### For Users
Go to **My Preferences > TwoFactor** to enable 2FA methods:
- Scan QR code for TOTP (authenticator apps)
- Enable email verification
- Add phone number for SMS
- Generate backup codes

Select your primary method and save.

### For Administrators
Go to **Extensions > TwoFactor Settings** to:
- Configure SMS Credits or Twilio API
- View SMS verification logs
- Access Pro features (if installed)

## [Upgrade to Pro](https://pixelsolutions.biz/plugins/twofactor/)

Unlock enterprise-grade security features with TwoFactor Pro:

- **Enforce 2FA** - Require all admin users to enable 2FA
- **Rate Limiting** - Protect against brute-force attacks
- **Trusted Devices** - Remember devices for 30 days
- **User Management** - Admin dashboard to manage all users
- **Security Alerts** - Email notifications for suspicious activity
- **IP Blacklisting** - Block malicious IP addresses
- **Email Templates** - Customize security notification emails

Visit [pixelsolutions.biz](https://pixelsolutions.biz/plugins/twofactor/) to purchase TwoFactor Pro.

## Requirements

- CMS Made Simple 2.1.6+
- PHP 7.4 or higher
- For TOTP: RobThree/TwoFactorAuth library (included)
- For SMS: Twilio account OR SMS Credits from Pixel Solutions

## Documentation

View complete documentation in **Extensions > TwoFactor > Help** tab.

## Support

- **Website:** https://pixelsolutions.biz
- **Email:** support@pixelsolutions.biz
- **Documentation:** https://pixelsolutions.biz/documentation/twofactor/

## License

MIT License - See LICENSE file for details
