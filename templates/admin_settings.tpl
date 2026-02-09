<h3>{$mod->Lang('tab_settings')}</h3>

{if $is_pro}
{form_start}
  
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
{form_end}
{else}
<div class="warning">
  <p>{$mod->Lang('pro_required_settings')}</p>
</div>
{/if}
