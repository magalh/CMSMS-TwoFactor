<h3>Hooks</h3>
<p>This module uses Hooks to tie in external functionality. The following hooks are available:</p>
<ul>
    <li><code>TwoFactor::BeforeVerification</code>
        <p>Sent before verification code is checked.</p>
        <p>Parameters (as an associative array)</p>
        <ul>
            <li><code>user_id</code> : int - The user id attempting verification</li>
            <li><code>provider</code> : string - Provider key</li>
            <li><code>code</code> : string - The verification code entered</li>
        </ul>
    </li>
    <li><code>TwoFactor::AfterVerificationSuccess</code>
        <p>Sent after successful verification.</p>
        <p>Parameters (as an associative array)</p>
        <ul>
            <li><code>user_id</code> : int - The user id who verified successfully</li>
            <li><code>provider</code> : string - Provider key used for verification</li>
        </ul>
    </li>
    <li><code>TwoFactor::AfterVerificationFail</code>
        <p>Sent after failed verification attempt.</p>
        <p>Parameters (as an associative array)</p>
        <ul>
            <li><code>user_id</code> : int - The user id who failed verification</li>
            <li><code>provider</code> : string - Provider key attempted</li>
            <li><code>code</code> : string - The incorrect code entered</li>
        </ul>
    </li>
</ul>

<h3>Permissions</h3>
<ul>
    <li><strong>Manage TwoFactor:</strong> Access to TwoFactor settings and configuration</li>
</ul>
