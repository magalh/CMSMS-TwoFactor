<p>Two-Factor Authentication (2FA) module for CMS Made Simple by Pixel Solutions.</p>

<h2>Features</h2>

<ul>
  <li><strong>Multiple Authentication Methods</strong>:
    <ul>
      <li>TOTP (Time-based One-Time Password) - Google Authenticator, Authy, etc.</li>
      <li>Email Verification - Receive codes via email</li>
      <li>SMS Verification - Receive codes via Twilio SMS</li>
      <li>Backup Codes - One-time emergency access codes</li>
    </ul>
  </li>
  <li><strong>Flexible Configuration</strong>:
    <ul>
      <li>Users can enable/disable methods individually</li>
      <li>Choose primary authentication method</li>
      <li>Multiple methods can be enabled as fallback options</li>
    </ul>
  </li>
  <li><strong>Security Features</strong>:
    <ul>
      <li>Intercepts login after username/password validation</li>
      <li>Session-based verification flow</li>
      <li>Audit logging for all 2FA events</li>
      <li>Secure code generation and validation</li>
    </ul>
  </li>
</ul>

<h2>Installation</h2>

<ol>
  <li>Upload the module files to <code>modules/TwoFactor/</code></li>
  <li>Install the module from Extensions &gt; Modules</li>
  <li>The module will automatically:
    <ul>
      <li>Create the database table <code>mod_twofactor_usermeta</code></li>
      <li>Copy <code>admin/twofactor.php</code> verification page</li>
      <li>Register the Core::LoginPost event handler</li>
    </ul>
  </li>
</ol>

<h2>Configuration</h2>

<h3>TOTP (Authenticator App)</h3>
<ol>
  <li>Navigate to Extensions &gt; TwoFactor</li>
  <li>Click "Configure" for Authenticator App</li>
  <li>Scan the QR code with your authenticator app</li>
  <li>Enter the 6-digit code to verify</li>
</ol>

<h3>Email Verification</h3>
<ol>
  <li>Click "Configure" for Email Verification</li>
  <li>Click "Enable Email Verification"</li>
  <li>Codes will be sent to your admin account email</li>
</ol>

<h3>SMS Verification (Twilio)</h3>
<ol>
  <li>Create a Twilio account and Verify Service</li>
  <li>Generate API Key and Secret in Twilio Console</li>
  <li>Enter credentials in SMS settings:
    <ul>
      <li>API Key SID</li>
      <li>API Secret</li>
      <li>Verify Service SID</li>
    </ul>
  </li>
  <li>Enter phone number in E.164 format (e.g., +1234567890)</li>
  <li>Verify the code sent to your phone</li>
</ol>

<h3>Backup Codes</h3>
<ol>
  <li>Click "Configure" for Backup Codes</li>
  <li>Click "Generate Backup Codes"</li>
  <li>Save the codes in a secure location</li>
  <li>Each code can only be used once</li>
</ol>

<h3>Screenshots</h3>
<p><img src="https://cmsms-downloads.s3.eu-south-1.amazonaws.com/TwoFactor/thumbnail.jpg" alt="TwoFactor module" width="900"></p>


<h2>Usage</h2>

<h3>Login Flow</h3>
<ol>
  <li>Enter username and password as normal</li>
  <li>After successful authentication, you'll be redirected to 2FA verification</li>
  <li>Enter the code from your primary authentication method</li>
  <li>Click "Use a backup code" if you need to use an alternative method</li>
</ol>

<h3>Switching Methods</h3>
<p>During verification, you can switch between enabled methods using the provider dropdown.</p>

<h2>Uninstallation</h2>

<p>The module will:</p>
<ul>
  <li>Remove the database table</li>
  <li>Delete the <code>admin/twofactor.php</code> file</li>
  <li>Remove all user 2FA settings</li>
</ul>

<h2>Requirements</h2>

<ul>
  <li>CMS Made Simple 2.x</li>
  <li>PHP 7.4 or higher</li>
  <li>For TOTP: RobThree/TwoFactorAuth library (included via Composer)</li>
  <li>For SMS: Twilio account with Verify API access</li>
</ul>

<h2>Support</h2>

<p>For issues or questions, visit: <a href="https://pixelsolutions.biz" target="_blank">https://pixelsolutions.biz</a></p>
