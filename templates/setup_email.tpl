<h3>{$mod->Lang('setup_email')}</h3>

{form_start}
{if $user_email}
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('email_setup_info', $user_email)}</p>
  </div>
  
  {if $is_enabled}
    <div class="pageoverflow">
      <p class="pageinput">
        <span class="information">{$mod->Lang('email_enabled_status')}</span>
      </p>
    </div>
  {/if}
  
  <div class="pageoverflow">
    <p class="pageinput">
      {if $is_enabled}
        <input type="submit" name="{$actionid}disable" value="{$mod->Lang('disable_email')}" />
      {else}
        <input type="submit" name="{$actionid}enable" value="{$mod->Lang('enable_email')}" />
      {/if}
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </p>
  </div>
{else}
  <div class="pageoverflow">
    <p class="pagetext" style="color:red;">{$mod->Lang('email_no_address')}</p>
  </div>
  
  <div class="pageoverflow">
    <p class="pageinput">
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </p>
  </div>
{/if}
{form_end}