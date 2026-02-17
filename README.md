# TwoFactor - Two-Factor Authentication for CMS Made Simple

Secure your CMS Made Simple installation with two-factor authentication (2FA) by Pixel Solutions.

**Already have TwoFactor Pro installed?** [View TwoFactor Pro Documentation]({cms_action_url module=ModuleManager action=defaultadmin modulehelp=TwoFactorPro})

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
- Audit logging for all 2FA events
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

## Configuration

### For Users (My Preferences > TwoFactor)

#### TOTP (Authenticator App)
1. Click "Configure" for Authenticator App
2. Scan the QR code with your authenticator app
3. Enter the 6-digit code to verify
4. Save your settings

#### Email Verification
1. Click "Configure" for Email Verification
2. Click "Enable Email Verification"
3. Codes will be sent to your admin account email

#### SMS Verification
**Using Managed SMS Credits:**
1. Admin must configure SMS Credits in TwoFactor Settings > SMS Settings
2. Click "Configure" for SMS Verification
3. Enter phone number in E.164 format (e.g., +1234567890)
4. Verify the code sent to your phone

**Using Twilio:**
1. Admin must configure Twilio in TwoFactor Settings > SMS Settings
2. Follow same steps as Managed SMS above

#### Backup Codes
1. Click "Configure" for Backup Codes
2. Click "Generate Backup Codes"
3. Save the codes in a secure location
4. Each code can only be used once

### For Administrators (TwoFactor Settings)

#### SMS Settings Tab

**Option 1: SMS Credits from Pixel Solutions**
1. Purchase SMS credits from https://pixelsolutions.biz
2. Enter your product key
3. Click "Save Product Key"
4. View your remaining credits and plan

**Option 2: Use Your Own Twilio Account**
1. Create a Twilio account and Verify Service
2. Generate API Key and Secret in Twilio Console
3. Enter credentials:
   - API Key SID
   - API Secret  
   - Verify Service SID
4. Click "Save Twilio Settings"

**Note:** You can have both configured. SMS Credits will be used first if available.

#### Verify Logs Tab
(Only visible when SMS Credits are configured)
- View SMS verification history
- See credits used per verification
- Monitor verification success/failure
- Paginated display (25 logs per page)

## Usage

### Login Flow
1. Enter username and password as normal
2. After successful authentication, you'll be redirected to 2FA verification
3. Enter the code from your primary authentication method
4. Click "Use a backup code" if you need to use an alternative method

### Switching Methods
During verification, you can switch between enabled methods using the provider dropdown.

## [Upgrade to Pro]({$product_url})

Unlock enterprise-grade security features with TwoFactor Pro:

- **Enforce 2FA** - Require all admin users to enable 2FA
- **Rate Limiting** - Protect against brute-force attacks
- **Trusted Devices** - Remember devices for 30 days
- **User Management** - Admin dashboard to manage all users
- **Security Alerts** - Email notifications for suspicious activity
- **IP Blacklisting** - Block malicious IP addresses
- **Email Templates** - Customize security notification emails

Visit [our store]({$product_url}) to purchase TwoFactor Pro.

**Already have TwoFactor Pro installed?** [View TwoFactor Pro Documentation]({cms_action_url module=ModuleManager action=defaultadmin modulehelp=TwoFactorPro})

## Requirements

- CMS Made Simple 2.1.6+
- PHP 7.4 or higher
- For TOTP: RobThree/TwoFactorAuth library (included via Composer)
- For SMS: Twilio account OR SMS Credits from Pixel Solutions

## Uninstallation

The module will:
- Remove the database table
- Delete the `admin/twofactor.php` file
- Remove all user 2FA settings

## Support

For issues or questions, visit: https://pixelsolutions.biz

Email: support@pixelsolutions.biz

## License

MIT License - See LICENSE file for details
