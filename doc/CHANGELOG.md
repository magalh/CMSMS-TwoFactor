# Changelog

## Version 4.0.0
- Improved: two-factor login now works smoothly with the modern CMS Made Simple login process (2.2.23 and newer), with no more forced logout-and-back-in behind the scenes
- Improved: the verification page and its links now work correctly whether or not your site uses pretty URLs
- Improved: choosing a different verification method is now clearer. Alternatives are shown as a tidy list of buttons with an icon for each method, instead of a cramped line of text links
- Fixed: entering your code could fail with a "Not Found" error on sites without pretty URLs. Verification now submits reliably in all setups
- Fixed: the "resend code", "use another method", "use a backup code", and "back to primary method" links now always work
- Fixed: choosing email or SMS verification no longer shows an error page when the message cannot be sent. You now get a clear message and can switch to another method
- Fixed: the backup codes verification screen could fail to load; it now displays correctly
- Changed: now requires CMS Made Simple 2.2.23 or newer, and PHP 8.1 or newer
- Housekeeping: upgrading from an older version and uninstalling now clean up leftover login settings automatically

## Version 3.0.0
- Added Passkey / WebAuthn (FIDO2) support as a new authentication method
- Built self-contained WebAuthn server library (no external dependencies)
- Includes CBOR decoder for FIDO2 attestation and COSE key parsing
- Platform authenticator support (Touch ID, Windows Hello, Face ID)
- Single passkey registration per user in free tier
- Auto-triggered browser authentication on verification page
- AJAX-based registration ceremony with real-time status feedback
- Passkey provider integrates with existing provider switching and backup codes
- Added WebAuthn challenge session management with proper cleanup
- HTTPS detection for WebAuthn availability
- Pro integration: authentication checks both base and Pro multi-key credentials
- Added ~40 new language strings for passkey and key management features

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
