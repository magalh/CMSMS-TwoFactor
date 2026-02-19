<h3>TwoFactor Pro Settings</h3>

<p>TwoFactor Pro extends the free version with enterprise-grade security features.</p>

<h3>License Activation</h3>
<ol>
  <li>Go to <strong>TwoFactor Settings > License</strong> tab</li>
  <li>Enter your license key</li>
  <li>Click "Verify License"</li>
</ol>

<h3>Pro Features Configuration</h3>

<h4>Enforce 2FA</h4>
<p>Force all admin users to enable two-factor authentication before accessing the admin panel.</p>
<ul>
  <li>Users without 2FA enabled will be redirected to setup page</li>
  <li>Cannot be bypassed once enabled</li>
</ul>

<h4>Rate Limiting</h4>
<p>Protect against brute-force attacks with exponential backoff.</p>
<ul>
  <li><strong>Max Attempts Before Lockout:</strong> Number of failed attempts before account is locked</li>
  <li><strong>Max Attempts Before Password Reset:</strong> Number of failed attempts before password reset is required</li>
  <li>Automatic exponential backoff delays between attempts</li>
</ul>

<h4>Trusted Devices</h4>
<p>Allow users to remember trusted devices for 30 days.</p>
<ul>
  <li>Users can check "Remember this device for 30 days" during verification</li>
  <li>Device fingerprinting for enhanced security</li>
  <li>Users can manage trusted devices in My Preferences</li>
  <li>Automatic cleanup of expired devices</li>
</ul>

<h4>Admin Notifications</h4>
<p>Receive email alerts for security events:</p>
<ul>
  <li>Multiple failed verification attempts</li>
  <li>Account lockouts</li>
  <li>Suspicious activity detection</li>
</ul>

<h4>IP Blacklist</h4>
<p>Block malicious IP addresses from accessing 2FA verification.</p>
<ul>
  <li>Enter one IP address per line</li>
  <li>Supports CIDR notation (e.g., 192.168.1.0/24)</li>
  <li>Blocked IPs cannot attempt verification</li>
</ul>

<h3>User Management</h3>

<p>View and manage all users' 2FA status from <strong>TwoFactor Settings > User Management</strong> tab:</p>
<ul>
  <li>See which users have 2FA enabled</li>
  <li>View primary authentication method for each user</li>
  <li>Reset user 2FA settings if needed</li>
  <li>Force enable/disable 2FA for specific users</li>
</ul>

<h3>Email Templates</h3>

<p>Customize security notification emails from <strong>TwoFactor Settings > Email Templates</strong> tab:</p>
<ul>
  <li><strong>Account Locked:</strong> Sent when user is locked out</li>
  <li><strong>Password Reset Required:</strong> Sent when password reset is triggered</li>
  <li><strong>Suspicious Activity:</strong> Sent when unusual activity is detected</li>
</ul>

<p>Available template variables:</p>
<ul>
  <li><code>{$username}</code> - The username</li>
  <li><code>{$ip_address}</code> - IP address of the attempt</li>
  <li><code>{$timestamp}</code> - Time of the event</li>
  <li><code>{$failed_attempts}</code> - Number of failed attempts</li>
</ul>
