<h3>{$mod->Lang('setup_backup_codes')}</h3>

{if $new_codes}
  <div class="pagemessage" style="background:#ffffcc;padding:15px;border:1px solid #ccc;">
    <p><strong>{$mod->Lang('backup_codes_warning')}</strong></p>
    <p>{$mod->Lang('backup_codes_info')}</p>
  </div>
  
  <div style="background:#f5f5f5;padding:20px;margin:20px 0;font-family:monospace;font-size:14px;">
    {foreach $codes as $code}
      <div style="padding:5px;">{$code}</div>
    {/foreach}
  </div>
  
{elseif $is_enabled}
  <p>{$mod->Lang('backup_codes_enabled_info')}</p>
  <p><strong>{$mod->Lang('codes_remaining')}:</strong> {count($codes)}</p>
  
  <form method="post" action="{cms_action_url action='setup_backup_codes'}">
    <input type="submit" name="{$actionid}generate" value="{$mod->Lang('regenerate_codes')}" 
           onclick="return confirm('{$mod->Lang('confirm_regenerate')}');" />
    <input type="submit" name="{$actionid}disable" value="{$mod->Lang('disable_backup_codes')}" 
           onclick="return confirm('{$mod->Lang('confirm_disable')}');" />
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
  </form>
  
{else}
  <p>{$mod->Lang('backup_codes_setup_info')}</p>
  
  <form method="post" action="{cms_action_url action='setup_backup_codes'}">
    <input type="submit" name="{$actionid}generate" value="{$mod->Lang('generate_codes')}" />
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
  </form>
  
{/if}
