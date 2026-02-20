<h3>{$mod->Lang('setup_backup_codes')}</h3>

{if $new_codes}
  <div class="pageoverflow">
    <p class="warning">
      <strong>{$mod->Lang('backup_codes_warning')}</strong><br/>
      {$mod->Lang('backup_codes_info')}
    </p>
  </div>
  
  <div class="pageoverflow">
    <p class="pageinput">
      <div style="background:#f5f5f5;padding:20px;font-family:monospace;font-size:14px;">
        {foreach $codes as $code}
          <div style="padding:5px;">{$code}</div>
        {/foreach}
      </div>
    </p>
  </div>
  
  {form_start action='setup_backup_codes'}
    <div class="pageoverflow">
      <p class="pageinput">
        <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('back')}" />
      </p>
    </div>
  {form_end}
  
{elseif $is_enabled}
  <div class="pageoverflow">
    <p class="information">{$mod->Lang('backup_codes_enabled_info')}</p>
  </div>
  
  <div class="pageoverflow">
    <p class="pagetext"><strong>{$mod->Lang('codes_remaining')}:</strong> {count($codes)}</p>
  </div>
  
  {form_start action='setup_backup_codes'}
    <div class="pageoverflow">
      <p class="pageinput">
        <input type="submit" name="{$actionid}generate" value="{$mod->Lang('regenerate_codes')}" 
               onclick="return confirm('{$mod->Lang('confirm_regenerate')}');" />
        <input type="submit" name="{$actionid}disable" value="{$mod->Lang('disable_backup_codes')}" 
               onclick="return confirm('{$mod->Lang('confirm_disable')}');" />
        <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
      </p>
    </div>
  {form_end}
  
{else}
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('backup_codes_setup_info')}</p>
  </div>
  
  {form_start action='setup_backup_codes'}
    <div class="pageoverflow">
      <p class="pageinput">
        <input type="submit" name="{$actionid}generate" value="{$mod->Lang('generate_codes')}" />
        <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
      </p>
    </div>
  {form_end}
  
{/if}
