<h3>{$mod->Lang('setup_totp')}</h3>

{if $error}
  <div class="warning">{$error}</div>
{/if}

{if $is_configured}
  <div class="pageoverflow">
    <p class="information">
      ✓ {$mod->Lang('totp_configured')}
    </p>
  </div>
  
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('totp_reset_warning')}</p>
  </div>
  
  {form_start action='setup_totp'}
    <div class="pageoverflow">
      <p class="pageinput">
        <input type="submit" name="{$actionid}reset" value="{$mod->Lang('reset_totp')}" 
               onclick="return confirm('{$mod->Lang('confirm_reset')}');" />
        <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
      </p>
    </div>
  {form_end}
{else}
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('scan_qr')}</p>
    <p class="pageinput">
      <img src="{$qr_code}" alt="QR Code" />
    </p>
  </div>
  
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('manual_entry')}:</p>
    <p class="pageinput">
      <code>{$secret}</code>
    </p>
  </div>
  
  {form_start action='setup_totp'}
    <input type="hidden" name="{$actionid}totp_key" value="{$secret}" />
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('enter_code_verify')}:</p>
      <p class="pageinput">
        <input type="text" inputmode="numeric" name="{$actionid}authcode" 
               value="" size="10" placeholder="123456" autocomplete="off" />
      </p>
    </div>
    
    <div class="pageoverflow">
      <p class="pageinput">
        <input type="submit" name="{$actionid}verify" value="{$mod->Lang('verify')}" />
        <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
      </p>
    </div>
  {form_end}
{/if}