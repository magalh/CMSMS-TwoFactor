<h3>{$mod->Lang('setup_totp')}</h3>

{if $error}
  <div class="pagemessage" style="color:red;">{$error}</div>
{/if}

{if $is_configured}
  <p class="pagetext" style="color:green;font-weight:bold;">
    ✓ {$mod->Lang('totp_configured')}
  </p>
  <p class="pagetext">{$mod->Lang('totp_reset_warning')}</p>
  {form_start action='setup_totp'}
    <p class="pageinput">
      <input type="submit" name="{$actionid}reset" value="{$mod->Lang('reset_totp')}" 
             onclick="return confirm('{$mod->Lang('confirm_reset')}');" />
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </p>
  {form_end}
{else}
  <p class="pagetext">{$mod->Lang('scan_qr')}</p>
  <p><img src="{$qr_code}" alt="QR Code" /></p>
  <p class="pagetext">{$mod->Lang('manual_entry')}: <code>{$secret}</code></p>
  
  <hr/>
  
  {form_start action='setup_totp'}
    <input type="hidden" name="{$actionid}totp_key" value="{$secret}" />
    <p class="pagetext">{$mod->Lang('enter_code_verify')}:</p>
    <p class="pageinput">
      <input type="text" inputmode="numeric" name="{$actionid}authcode" 
             value="" size="10" placeholder="123456" autocomplete="off" />
    </p>
    <p class="pageinput">
      <input type="submit" name="{$actionid}verify" value="{$mod->Lang('verify')}" />
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </p>
  {form_end}
{/if}

<p class="pageback">
  <a class="pageback" href="{cms_action_url action='defaultadmin'}">&laquo; {$mod->Lang('back')}</a>
</p>
