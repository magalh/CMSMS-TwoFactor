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
    <div class="passkey-card" style="border: 1px solid #ddd; border-radius: 6px; padding: 12px 15px; margin-bottom: 10px; background: #fafafa;">
      <div style="display: flex; align-items: center; gap: 12px;">
        {if $card.authenticator === 'Windows Hello'}
        <img src="{$mod_url}/assets/canonical-passkey-icon-hello.svg" alt="" width="32" height="32" />
        {else}
        <img src="{$mod_url}/assets/canonical-passkey-icon.svg" alt="" width="32" height="32" />
        {/if}
        <div style="flex: 1;">
          <strong>{$card.name}</strong>
          <div style="font-size: 12px; color: #666; margin-top: 2px;">
            {if $card.authenticator}{$card.authenticator}{else}{if $card.type === 'cross-platform'}{$mod->Lang('passkey_type_cross_platform')}{else}{$mod->Lang('passkey_type_platform')}{/if}{/if}
          </div>
        </div>
        <a href="#" class="passkey-remove" data-source="{$card.source}" data-id="{$card.id}" title="{$mod->Lang('passkey_remove_key')}" style="color: #c00; font-size: 12px; text-decoration: none;">{admin_icon icon='delete.gif'} {$mod->Lang('passkey_remove_key')}</a>
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
          {if $is_pro}
            <input type="submit" id="btn-add-passkey" name="{$actionid}add_key" value="{$mod->Lang('passkey_add_key')}" />
          {/if}
          <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
        </p>
      </div>
    {form_end}

    <script>
    {literal}
    $(function() {
      var freeAjaxUrl = '{/literal}{cms_action_url action="ajax_passkey" forjs=1}{literal}';
      var proAjaxUrl = '{/literal}{if $is_pro}{cms_action_url action="ajax_pro_passkey" module="TwoFactorPro" forjs=1}{/if}{literal}';
      var actionId = '{/literal}{$actionid}{literal}';

      $('a.passkey-remove').on('click', function(e) {
        e.preventDefault();
        if (!confirm('{/literal}{$mod->Lang('confirm_remove_key')}{literal}')) return;

        var $link = $(this);
        var source = $link.data('source');
        var id = $link.data('id');
        var url = (source === 'pro') ? proAjaxUrl : freeAjaxUrl;
        var data = {};
        data[actionId + 'op'] = 'remove';
        if (source === 'pro') data[actionId + 'key_id'] = id;

        $link.text('{/literal}{$mod->Lang('passkey_removing')}{literal}');

        $.ajax({
          url: url,
          type: 'POST',
          data: data,
          dataType: 'json'
        }).done(function(result) {
          if (result.success) {
            $link.closest('.passkey-card').fadeOut(300, function() {
              $(this).remove();
              if ($('.passkey-card').length === 0) {
                window.location.reload();
              }
            });
          } else {
            alert(result.error || '{/literal}{$mod->Lang('passkey_remove_failed')}{literal}');
            $link.html('{/literal}{admin_icon icon="delete.gif"} {$mod->Lang("passkey_remove_key")}{literal}');
          }
        }).fail(function() {
          alert('{/literal}{$mod->Lang('passkey_remove_failed')}{literal}');
          $link.html('{/literal}{admin_icon icon="delete.gif"} {$mod->Lang("passkey_remove_key")}{literal}');
        });
      });
    });
    {/literal}
    </script>

    {if $is_pro}
    <div class="pageoverflow" id="add-key-status-row" style="display:none;">
      <p class="pageinput">
        <span id="add-key-status"></span>
      </p>
    </div>

    <script>
    {literal}
    $(function() {
      var proRegUrl = '{/literal}{cms_action_url action="ajax_pro_passkey" module="TwoFactorPro" forjs=1}{literal}';
      var actionId = '{/literal}{$actionid}{literal}';
      var $btn = $('#btn-add-passkey');
      if (!$btn.length) return;

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

      function setAddStatus(msg) {
        $('#add-key-status-row').show();
        $('#add-key-status').text(msg);
      }

      $btn.on('click', function(e) {
        e.preventDefault();
        $btn.prop('disabled', true);

        var keyType = $('#pro_key_type').val();
        var regData = {};
        regData[actionId + 'op'] = 'get_reg_options';
        regData[actionId + 'key_type'] = keyType;

        $.ajax({
          url: proRegUrl,
          type: 'POST',
          data: regData,
          dataType: 'json'
        }).done(function(options) {
          options.challenge = base64UrlToBuffer(options.challenge);
          options.user.id = base64UrlToBuffer(options.user.id);
          if (options.excludeCredentials) {
            $.each(options.excludeCredentials, function(i, c) {
              c.id = base64UrlToBuffer(c.id);
            });
          }

          setAddStatus('{/literal}{$mod->Lang('passkey_reg_waiting')}{literal}');

          navigator.credentials.create({ publicKey: options }).then(function(credential) {
            var response = JSON.stringify({
              clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
              attestationObject: bufferToBase64Url(credential.response.attestationObject)
            });

            setAddStatus('{/literal}{$mod->Lang('passkey_reg_saving')}{literal}');

            var saveData = {};
            saveData[actionId + 'op'] = 'register';
            saveData[actionId + 'key_type'] = keyType;
            saveData[actionId + 'webauthn_response'] = response;

            $.ajax({
              url: proRegUrl,
              type: 'POST',
              data: saveData,
              dataType: 'json'
            }).done(function(result) {
              if (result.success) {
                setAddStatus('{/literal}{$mod->Lang('passkey_reg_success')}{literal}');
                setTimeout(function() { window.location.reload(); }, 800);
              } else {
                setAddStatus(result.error || '{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
                $btn.prop('disabled', false);
              }
            }).fail(function() {
              setAddStatus('{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
              $btn.prop('disabled', false);
            });

          }, function(err) {
            setAddStatus('{/literal}{$mod->Lang('passkey_reg_cancelled')}{literal}');
            $btn.prop('disabled', false);
          });

        }).fail(function() {
          setAddStatus('{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
          $btn.prop('disabled', false);
        });
      });
    });
    {/literal}
    </script>
    {/if}

  {else}
    <div class="pageoverflow" style="padding: 10px 0;">
      <img src="{$mod_url}/assets/canonical-passkey-icon.svg" alt="Passkey" width="48" height="48" />
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
      var regUrl = '{/literal}{cms_action_url action="ajax_passkey" forjs=1}{literal}';
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

      function setRegStatus(msg) {
        $('#passkey-status-row').show();
        $('#passkey-status').text(msg);
      }

      if (!window.PublicKeyCredential) {
        $btn.prop('disabled', true);
        setRegStatus('{/literal}{$mod->Lang('passkey_unsupported')}{literal}');
        return;
      }

      $btn.on('click', function(e) {
        e.preventDefault();
        $btn.prop('disabled', true);

        var initData = {};
        initData[actionId + 'op'] = 'get_reg_options';

        $.ajax({
          url: regUrl,
          type: 'POST',
          data: initData,
          dataType: 'json'
        }).done(function(options) {
          options.challenge = base64UrlToBuffer(options.challenge);
          options.user.id = base64UrlToBuffer(options.user.id);
          if (options.excludeCredentials) {
            $.each(options.excludeCredentials, function(i, c) {
              c.id = base64UrlToBuffer(c.id);
            });
          }

          setRegStatus('{/literal}{$mod->Lang('passkey_reg_waiting')}{literal}');

          navigator.credentials.create({ publicKey: options }).then(function(credential) {
            var response = JSON.stringify({
              clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
              attestationObject: bufferToBase64Url(credential.response.attestationObject)
            });

            setRegStatus('{/literal}{$mod->Lang('passkey_reg_saving')}{literal}');

            var saveData = {};
            saveData[actionId + 'op'] = 'register';
            saveData[actionId + 'webauthn_response'] = response;

            $.ajax({
              url: regUrl,
              type: 'POST',
              data: saveData,
              dataType: 'json'
            }).done(function(result) {
              if (result.success) {
                setRegStatus('{/literal}{$mod->Lang('passkey_reg_success')}{literal}');
                setTimeout(function() { window.location.href = successUrl; }, 800);
              } else {
                setRegStatus(result.error || '{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
                $btn.prop('disabled', false);
              }
            }).fail(function() {
              setRegStatus('{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
              $btn.prop('disabled', false);
            });

          }, function(err) {
            setRegStatus('{/literal}{$mod->Lang('passkey_reg_cancelled')}{literal}');
            $btn.prop('disabled', false);
          });

        }).fail(function() {
          setRegStatus('{/literal}{$mod->Lang('passkey_reg_failed')}{literal}');
          $btn.prop('disabled', false);
        });
      });
    });
    {/literal}
    </script>
  {/if}
{/if}
