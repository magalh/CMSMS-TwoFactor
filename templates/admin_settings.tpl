<h3>{$mod->Lang('tab_settings')}</h3>

{form_start}
  
  <fieldset>
    <legend>{$mod->Lang('general_settings')}</legend>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('enforce_2fa_all')}:</p>
      <p class="pageinput">
        <input type="checkbox" name="{$actionid}enforce_2fa_all" value="1"{if $enforce_2fa_all == '1'} checked{/if} />
        {$mod->Lang('enforce_2fa_all_help')}
      </p>
    </div>
  </fieldset>

{if $is_pro}
  <fieldset>
    <legend>{$mod->Lang('rate_limiting_settings')}</legend>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('rate_limiting_enabled')}:</p>
      <p class="pageinput">
        <input type="checkbox" name="{$actionid}rate_limiting_enabled" value="1"{if $rate_limiting_enabled == '1'} checked{/if} />
        {$mod->Lang('rate_limiting_enabled_help')}
      </p>
    </div>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('max_attempts_lockout')}:</p>
      <p class="pageinput">
        <input type="number" name="{$actionid}max_attempts_lockout" value="{$max_attempts_lockout}" min="1" max="20" />
        {$mod->Lang('max_attempts_lockout_help')}
      </p>
    </div>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('max_attempts_reset')}:</p>
      <p class="pageinput">
        <input type="number" name="{$actionid}max_attempts_reset" value="{$max_attempts_reset}" min="5" max="50" />
        {$mod->Lang('max_attempts_reset_help')}
      </p>
    </div>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('notify_admin')}:</p>
      <p class="pageinput">
        <input type="checkbox" name="{$actionid}notify_admin" value="1"{if $notify_admin == '1'} checked{/if} />
        {$mod->Lang('notify_admin_help')}
      </p>
    </div>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('ip_blacklist')}:</p>
      <p class="pageinput">
        <textarea name="{$actionid}ip_blacklist" rows="3" cols="50">{$ip_blacklist|escape}</textarea><br/>
        {$mod->Lang('ip_blacklist_help')}
      </p>
    </div>
  </fieldset>
  
  <div class="pageoverflow" style="margin-top:20px;">
    <p class="pagetext"></p>
    <p class="pageinput">
      <input type="submit" name="{$actionid}save_settings" value="{$mod->Lang('save_settings')}" class="pagebutton" />
    </p>
  </div>
{else}
</fieldset>

<div class="warning" style="margin-top:20px;">
  <p>{$mod->Lang('pro_required_settings')}</p>
</div>

<div class="pageoverflow" style="margin-top:20px;">
  <p class="pagetext"></p>
  <p class="pageinput">
    <input type="submit" name="{$actionid}save_settings" value="{$mod->Lang('save_settings')}" class="pagebutton" />
  </p>
</div>
{/if}
{form_end}
