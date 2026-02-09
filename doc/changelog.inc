<h3>Version 1.1.3</h3>
<ul>
  <li>Added dual permission system (manage_twofactor and use_twofactor)</li>
  <li>Created separate admin interfaces: Site Admin settings and My Preferences</li>
  <li>Centralized Twilio API configuration in site preferences</li>
  <li>Added "Disabled" option to primary method dropdown</li>
  <li>Excluded backup codes from primary method selection</li>
  <li>Improved SMS setup with verified status display</li>
  <li>Converted to standard CMSMS message system (SetMessage/SetError)</li>
  <li>Added customizable HTML email template system</li>
  <li>Created professional email verification template with responsive design</li>
</ul>

<h3>Version 1.0.0</h3>
<ul>
  <li>Initial stable release</li>
  <li>TOTP (Authenticator App) support with QR code generation</li>
  <li>Email verification with 6-digit codes</li>
  <li>SMS verification via Twilio Verify API</li>
  <li>Backup codes for emergency access</li>
  <li>Phone number verification flow with resend and change options</li>
  <li>Friendly error messages for Twilio API errors</li>
  <li>Login interception via Core::LoginPost event</li>
  <li>Standalone verification page (admin/twofactor.php)</li>
  <li>User metadata storage system</li>
  <li>Provider switching during verification</li>
  <li>Primary method selection</li>
  <li>Audit logging for all 2FA events</li>
</ul>
