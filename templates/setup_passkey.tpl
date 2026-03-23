<h3>{$mod->Lang('setup_passkey')}</h3>

{if !$webauthn_supported}
  <div class="warning">
    <p>{$mod->Lang('passkey_requires_https')}</p>
  </div>
{else}

  {if $error}
    <div class="warning">{$error}</div>
  {/if}

  {if $is_configured}
    <div class="pageoverflow">
      <p class="information">
        ✓ {$mod->Lang('passkey_configured')}
      </p>
    </div>

    {if $credential.name}
      <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('passkey_name')}: <strong>{$credential.name}</strong></p>
        <p class="pagetext">{$mod->Lang('passkey_registered')}: {$credential.created_at|date_format:'%Y-%m-%d %H:%M'}</p>
      </div>
    {/if}

    {if $is_pro}
      <div class="information">
        <p>{$mod->Lang('passkey_pro_multi_key')}</p>
      </div>
    {/if}

    {form_start action='setup_passkey'}
      <div class="pageoverflow">
        <p class="pageinput">
          <input type="submit" name="{$actionid}reset" value="{$mod->Lang('reset_passkey')}"
                 onclick="return confirm('{$mod->Lang('confirm_reset_passkey')}');" />
          <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
        </p>
      </div>
    {form_end}

  {else}
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('passkey_setup_info')}</p>
    </div>

    <div id="passkey-register-area">
      <div class="pageoverflow">
        <p class="pageinput">
          <button type="button" id="btn-register-passkey" class="pagebutton">
            {$mod->Lang('register_passkey')}
          </button>
          <span id="passkey-status" style="margin-left: 10px;"></span>
        </p>
      </div>
    </div>

    <div class="pageoverflow">
      <p class="pageinput">
        <a href="{cms_action_url action='user_prefs'}">{$mod->Lang('cancel')}</a>
      </p>
    </div>

    <script>
    {literal}
    (function() {
      var regOptionsUrl = '{/literal}{cms_action_url action="setup_passkey"}{literal}';
      var registerUrl = '{/literal}{cms_action_url action="setup_passkey"}{literal}';
      var successUrl = '{/literal}{cms_action_url action="user_prefs"}{literal}';
      var actionId = '{/literal}{$actionid}{literal}';

      function base64UrlToBuffer(base64url) {
        var base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        while (base64.length % 4) base64 += '=';
        var binary = atob(base64);
        var bytes = new Uint8Array(binary.length);
        for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
        return bytes.buffer;
      }

      function bufferToBase64Url(buffer) {
        var bytes = new Uint8Array(buffer);
        var binary = '';
        for (var i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
      }

      document.getElementById('btn-register-passkey').addEventListener('click', function() {
        var statusEl = document.getElementById('passkey-status');
        statusEl.textContent = 'Requesting options...';

        // Get registration options from server
        var form = new FormData();
        form.append(actionId + 'get_reg_options', '1');

        fetch(regOptionsUrl, { method: 'POST', body: form })
          .then(function(r) { return r.json(); })
          .then(function(options) {
            // Convert base64url fields to ArrayBuffers
            options.challenge = base64UrlToBuffer(options.challenge);
            options.user.id = base64UrlToBuffer(options.user.id);
            if (options.excludeCredentials) {
              options.excludeCredentials.forEach(function(c) {
                c.id = base64UrlToBuffer(c.id);
              });
            }

            statusEl.textContent = 'Waiting for authenticator...';

            return navigator.credentials.create({ publicKey: options });
          })
          .then(function(credential) {
            var response = {
              clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
              attestationObject: bufferToBase64Url(credential.response.attestationObject)
            };

            statusEl.textContent = 'Verifying...';

            var form2 = new FormData();
            form2.append(actionId + 'register_passkey', '1');
            form2.append(actionId + 'webauthn_response', JSON.stringify(response));

            return fetch(registerUrl, { method: 'POST', body: form2 });
          })
          .then(function(r) { return r.json(); })
          .then(function(result) {
            if (result.success) {
              statusEl.textContent = 'Success!';
              window.location.href = successUrl;
            } else {
              statusEl.textContent = 'Error: ' + (result.error || 'Registration failed');
            }
          })
          .catch(function(err) {
            statusEl.textContent = 'Error: ' + err.message;
            console.error('Passkey registration error:', err);
          });
      });

      // Check browser support
      if (!window.PublicKeyCredential) {
        document.getElementById('btn-register-passkey').disabled = true;
        document.getElementById('passkey-status').textContent = '{/literal}{$mod->Lang('passkey_browser_unsupported')}{literal}';
      }
    })();
    {/literal}
    </script>
  {/if}
{/if}
