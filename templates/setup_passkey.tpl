<h3>{$mod->Lang('setup_passkey')}</h3>

{if !$webauthn_supported}
  <div class="warning">
    <p>{$mod->Lang('passkey_requires_https')}</p>
  </div>
{else}

  {if $error}
    <div class="warning">
      <p>{$error}</p>
    </div>
  {/if}

  {if $is_configured}

    {foreach $passkey_cards as $card}
    <div style="border: 1px solid #ddd; border-radius: 6px; padding: 12px 15px; margin-bottom: 10px; background: #fafafa;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <img src="{$mod_url}/assets/canonical-passkey-icon.png" alt="" width="32" height="32" />
        <div style="flex: 1;">
          <strong>{$card.name}</strong>
          <div style="font-size: 12px; color: #666; margin-top: 2px;">
            {if $card.type === 'cross-platform'}{$mod->Lang('passkey_type_cross_platform')}{else}{$mod->Lang('passkey_type_platform')}{/if}
          </div>
        </div>
      </div>
      <div style="font-size: 12px; color: #888; margin-top: 8px; padding-left: 44px;">
        {$mod->Lang('passkey_created')}: {$card.created_at|date_format:'%b %d, %Y'}
        &nbsp;&middot;&nbsp;
        {$mod->Lang('passkey_last_used')}: {if $card.last_used_at > 0}{$card.last_used_at|date_format:'%b %d, %Y'}{else}{$mod->Lang('passkey_never_used')}{/if}
      </div>
    </div>
    {/foreach}

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
    <div class="pageoverflow" style="padding: 10px 0;">
      <img src="{$mod_url}/assets/canonical-passkey-icon.png" alt="Passkey" width="48" height="48" />
      <p>{$mod->Lang('passkey_setup_info')}</p>
    </div>

    {form_start action='setup_passkey'}
      <div class="pageoverflow">
        <p class="pageinput">
          <input type="submit" id="btn-register-passkey" name="{$actionid}register" value="{$mod->Lang('register_passkey')}" />
          <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
        </p>
      </div>
    {form_end}

    <div class="pageoverflow" id="passkey-status-row" style="display:none;">
      <p class="pageinput">
        <span id="passkey-status"></span>
      </p>
    </div>

    <script>
    {literal}
    $(function() {
      var ajaxUrl = '{/literal}{cms_action_url action="ajax_passkey" forjs=1}{literal}';
      var actionId = '{/literal}{$actionid}{literal}';
      var successUrl = '{/literal}{cms_action_url action="user_prefs" forjs=1}{literal}';
      var $btn = $('#btn-register-passkey');

      function base64UrlToBuffer(b) {
        var s = b.replace(/-/g, '+').replace(/_/g, '/');
        while (s.length % 4) s += '=';
        var bin = atob(s), arr = new Uint8Array(bin.length);
        for (var i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
        return arr.buffer;
      }

      function bufferToBase64Url(buf) {
        var arr = new Uint8Array(buf), s = '';
        for (var i = 0; i < arr.length; i++) s += String.fromCharCode(arr[i]);
        return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
      }

      function setStatus(msg) {
        $('#passkey-status-row').show();
        $('#passkey-status').text(msg);
      }

      if (!window.PublicKeyCredential) {
        $btn.prop('disabled', true);
        setStatus('{/literal}{$mod->Lang('passkey_unsupported')}{literal}');
        return;
      }

      $btn.on('click', function(e) {
        e.preventDefault();
        $btn.prop('disabled', true);

        var data = {};
        data[actionId + 'op'] = 'get_reg_options';

        $.ajax({
          url: ajaxUrl,
          type: 'POST',
          data: data,
          dataType: 'json'
        }).done(function(options) {
          options.challenge = base64UrlToBuffer(options.challenge);
          options.user.id = base64UrlToBuffer(options.user.id);
          if (options.excludeCredentials) {
            $.each(options.excludeCredentials, function(i, c) {
              c.id = base64UrlToBuffer(c.id);
            });
          }

          setStatus('{/literal}{$mod->Lang('passkey_reg_waiting')}{literal}');

          navigator.credentials.create({ publicKey: options }).then(function(credential) {
            var response = JSON.stringify({
              clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
              attestationObject: bufferToBase64Url(credential.response.attestationObject)
            });

            setStatus('{/literal}{$mod->Lang('passkey_reg_saving')}{literal}');

            var data2 = {};
            data2[actionId + 'op'] = 'register';
            data2[actionId + 'webauthn_response'] = response;

            $.ajax({
              url: ajaxUrl,
              type: 'POST',
              data: data2,
              dataType: 'json'
            }).done(function(result) {
              if (result.success) {
                setStatus('{/literal}{$mod->Lang('passkey_reg_success')}{literal}');
                setTimeout(function() { window.location.href = successUrl; }, 800);
              } else {
                setStatus(result.error || '{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
                $btn.prop('disabled', false);
              }
            }).fail(function() {
              setStatus('{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
              $btn.prop('disabled', false);
            });

          }, function(err) {
            setStatus('{/literal}{$mod->Lang('passkey_reg_cancelled')}{literal}');
            $btn.prop('disabled', false);
          });

        }).fail(function() {
          setStatus('{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
          $btn.prop('disabled', false);
        });
      });
    });
    {/literal}
    </script>
  {/if}
{/if}
