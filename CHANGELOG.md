# Changelog

## Version 2.1.0
- Migrated verification flow to module action (removed standalone admin/twofactor.php)
- Added language string support for all verification templates (TOTP, Email, SMS)
- Improved form field handling with actionid prefixes for proper parameter passing
- Enhanced rate limiting with automatic cleanup when disabled
- Updated all providers to accept $params parameter for consistent validation
- Improved countdown timer with language-based messages
- Added message display support for resend notifications
- Enhanced form security with CSRF token validation

## Version 2.0.0
- Split module into TwoFactor (Free) and TwoFactorPro (Premium)
- Added Managed SMS Credits system
- Added SMS verification logs with pagination
- Improved admin interface with unified settings
- Added event system for Pro integration
- Enhanced security with license validation
- Added upgrade path to Pro version

## Version 1.1.3
- Added dual permission system (manage_twofactor and use_twofactor)
- Created separate admin interfaces: Site Admin settings and My Preferences
- Centralized Twilio API configuration in site preferences
- Added "Disabled" option to primary method dropdown
- Excluded backup codes from primary method selection
- Improved SMS setup with verified status display
- Converted to standard CMSMS message system (SetMessage/SetError)
- Added customizable HTML email template system
- Created professional email verification template with responsive design

## Version 1.0.0
- Initial stable release
- TOTP (Authenticator App) support with QR code generation
- Email verification with 6-digit codes
- SMS verification via Twilio Verify API
- Backup codes for emergency access
- Phone number verification flow with resend and change options
- Friendly error messages for Twilio API errors
- Login interception via Core::LoginPost event
- Standalone verification page (admin/twofactor.php)
- User metadata storage system
- Provider switching during verification
- Primary method selection
- Audit logging for all 2FA events
