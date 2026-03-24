=== Two Factor ===
Contributors: Pixel Solutions
Tags: security, two-factor, authentication, 2fa, login
Requires at least: 2.2.1
Tested up to: 2.2.22
Stable tag: 3.0.0
Requires PHP: 7.4
License: MIT

Secure your CMS Made Simple login with two-factor authentication (2FA).

== Description ==

TwoFactor adds two-factor authentication to your CMS Made Simple admin login. Supports multiple authentication methods including TOTP (authenticator apps), Passkeys (WebAuthn/FIDO2), Email, SMS, and Backup Codes.

**Multiple Authentication Methods**

- TOTP (Time-based One-Time Password) — Google Authenticator, Authy, Microsoft Authenticator, etc.
- Passkey / WebAuthn — Windows Hello, Touch ID, Face ID, Android biometrics
- Email Verification — Receive codes via email
- SMS Verification — Receive codes via SMS (Twilio or Managed SMS Credits)
- Backup Codes — One-time emergency access codes

**Passkey Support (WebAuthn / FIDO2)**

- Built-in WebAuthn server — no external dependencies
- One passkey per user (free tier)
- Auto-detects authenticator type via AAGUID
- Works with Touch ID, Windows Hello, Face ID, and device PIN

**Flexible Configuration**

- Users can enable/disable methods individually
- Choose primary authentication method
- Multiple methods can be enabled as fallback options
- "Use a different method" links on all verification pages

**SMS Options**

Option 1: Managed SMS Credits — Purchase SMS credits from Pixel Solutions. Simple setup, no API configuration needed.

Option 2: Your Own Twilio Account — Use your existing Twilio account with full control over SMS delivery.

**Upgrade to Pro**

Unlock enterprise-grade security features with [TwoFactor Pro](https://pixelsolutions.biz/plugins/twofactor/):

- Unlimited Passkeys — Register multiple passkeys per user
- Physical Security Keys — YubiKey, Titan Key, USB/NFC as a standalone login method
- Security Key as Primary Method — Separate from Passkey, selectable as primary 2FA
- AAGUID Detection — Auto-identifies authenticator make/model
- Enforce 2FA — Require all admin users to enable 2FA
- Rate Limiting — Protect against brute-force attacks
- Trusted Devices — Remember devices for 30 days
- User Management — Admin dashboard to manage all users
- Security Alerts — Email notifications for suspicious activity
- IP Blacklisting — Block malicious IP addresses
- Email Templates — Customize security notification emails

== Installation ==

1. Upload the TwoFactor module files to modules/TwoFactor/
2. Install the module from Extensions > Modules
3. Grant users the "Use TwoFactor" permission
4. Users can enable 2FA from My Preferences > TwoFactor

== Screenshots ==

1. User preferences — enable and configure authentication methods
2. Admin settings — configure SMS credits and Twilio API

== Changelog ==

= 3.0.0 =
- Added Passkey / WebAuthn (FIDO2) support as a new authentication method
- Built self-contained WebAuthn server library (no external dependencies)
- Platform authenticator support (Touch ID, Windows Hello, Face ID)
- Single passkey registration per user in free tier
- AJAX-based registration ceremony with real-time status feedback
- Pro integration: authentication checks both base and Pro multi-key credentials

= 2.1.0 =
- Migrated verification flow to module action
- Added language string support for all verification templates
- Enhanced rate limiting with automatic cleanup
- Improved countdown timer with language-based messages

= 2.0.0 =
- Split module into TwoFactor (Free) and TwoFactorPro (Premium)
- Added Managed SMS Credits system
- Added SMS verification logs with pagination
- Added event system for Pro integration

= 1.0.0 =
- Initial stable release
- TOTP, Email, SMS, and Backup Codes support
- Login interception via Core::LoginPost event
