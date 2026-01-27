<h3>{$mod->Lang('setup_email')}</h3>

{if $user_email}
  <p>{$mod->Lang('email_setup_info', $user_email)}</p>
  
  {if $is_enabled}
    <p style="color:green;">{$mod->Lang('email_enabled_status')}</p>
    <form method="post" action="{cms_action_url action='setup_email'}">
      <input type="submit" name="{$actionid}disable" value="{$mod->Lang('disable_email')}" />
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </form>
  {else}
    <form method="post" action="{cms_action_url action='setup_email'}">
      <input type="submit" name="{$actionid}enable" value="{$mod->Lang('enable_email')}" />
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </form>
  {/if}
{else}
  <p style="color:red;">{$mod->Lang('email_no_address')}</p>
  <form method="post" action="{cms_action_url action='setup_email'}">
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
  </form>
{/if}

<p><a class="pageback" href="{cms_action_url action='defaultadmin'}">&laquo; {$mod->Lang('back')}</a></p>
