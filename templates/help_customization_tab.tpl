<h3>Template Customization</h3>

<p>TwoFactor creates email templates that you can customize in <strong>Layout > Design Manager</strong>.</p>

<h3>Email Templates</h3>

<h4>Email Verification</h4>
<p><strong>Template Type:</strong> TwoFactor::Email Verification</p>
<p>Customize the email design sent to users when they request an email verification code.</p>
<p>Find this template in <strong>Layout > Design Manager</strong> under the "TwoFactor::Email Verification" type.</p>

{if $have_2fpro}
<h3>TwoFactor Pro Email Templates</h3>

<p>TwoFactor Pro adds additional customizable email templates. Configure these in <strong>TwoFactor Settings > Templates</strong> tab:</p>

<h4>Password Reset Email</h4>
<p>Sent when a user is forced to reset their password after excessive failed attempts.</p>
<p><strong>Available placeholders:</strong> <code>{literal}{username}, {reset_url}{/literal}</code></p>

<h4>Admin Alert Email</h4>
<p>Sent to administrators when suspicious activity is detected.</p>
<p><strong>Available placeholders:</strong> <code>{literal}{username}, {ip_address}, {attempts}, {time}{/literal}</code></p>
{/if}
