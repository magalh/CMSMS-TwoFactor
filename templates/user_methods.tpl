<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
  <h3 style="margin:0;">{$mod->Lang('two_factor_settings')}</h3>
  <a href="https://pixelsolutions.biz" target="_blank" rel="noopener noreferrer">
    <img src="https://pixelsolution.s3.eu-south-1.amazonaws.com/logos/LOGO_3_COLOR_300.png" alt="Pixel Solutions" style="height:40px;" />
  </a>
</div>

{if $enabled_providers}
  <div class="information">
    <p>{$mod->Lang('two_factor_active')}</p>
  </div>
  
  <p class="pagetext">{$mod->Lang('primary_method_info')}:</p>
  <form method="post" action="{cms_action_url action='user_prefs' active_tab='methods'}" style="margin-bottom:20px;">
    <div class="pageoverflow">
      
      <p class="pageinput">
        <select name="{$actionid}set_primary" id="primary_method">
          <option value="disabled"{if $primary_provider == 'disabled'} selected{/if}>{$mod->Lang('disabled')}</option>
          {foreach $providers as $key => $provider}
            {if in_array($key, $enabled_providers) && $key != 'TwoFactorProviderBackupCodes'}
              <option value="{$key}"{if $key == $primary_provider} selected{/if}>{$provider->get_label()}</option>
            {/if}
          {/foreach}
        </select>
        <input type="submit" value="{$mod->Lang('update')}" class="pagebutton" />
      </p>
    </div>
  </form>
{else}
  <div class="warning">
    <p>{$mod->Lang('no_methods_enabled')}</p>
  </div>
{/if}

<h4>{$mod->Lang('available_methods')}</h4>
<table class="pagetable">
  <thead>
    <tr>
      <th>{$mod->Lang('method')}</th>
      <th class="pageicon">{* edit icon *}</th>
      <th class="pageicon">{* edit icon *}</th>
    </tr>
  </thead>
  <tbody>
    <tr >
      <td class="p_top_10 p_bottom_10"><strong>{$mod->Lang('provider_totp')}</strong><br/><small>{$mod->Lang('totp_description')}</small></td>
      <td>
        {if in_array('TwoFactorProviderTOTP', $enabled_providers)}
          {admin_icon title='Enabled' icon='true.gif'}
        {else}
          {admin_icon title='Disabled' icon='false.gif'}
        {/if}
      </td>
      <td>
        <a href="{cms_action_url action='setup_totp'}">{admin_icon icon='edit.gif'}</a>
      </td>
    </tr>
    <tr class="row2">
      <td class="p_top_10 p_bottom_10"><strong>{$mod->Lang('provider_email')}</strong><br/><small>{$mod->Lang('email_description')}</small></td>
      <td>
        {if in_array('TwoFactorProviderEmail', $enabled_providers)}
          {admin_icon title='Enabled' icon='true.gif'}
        {else}
          {admin_icon title='Disabled' icon='false.gif'}
        {/if}
      </td>
      <td>
        <a href="{cms_action_url action='setup_email'}">{admin_icon icon='edit.gif'}</a>
      </td>
    </tr>
    <tr>
      <td class="p_top_10 p_bottom_10"><strong>{$mod->Lang('provider_sms')}</strong><br/><small>{$mod->Lang('sms_description')}</small></td>
      <td>
        {if in_array('TwoFactorProviderSMS', $enabled_providers)}
          {admin_icon title='Enabled' icon='true.gif'}
        {else}
          {admin_icon title='Disabled' icon='false.gif'}
        {/if}
      </td>
      <td>
        <a href="{cms_action_url action='setup_sms'}">{admin_icon icon='edit.gif'}</a>
      </td>
    </tr>
    <tr class="row2">
      <td class="p_top_10 p_bottom_10"><strong>{$mod->Lang('provider_backup_codes')}</strong><br/><small>{$mod->Lang('backup_codes_description')}</small></td>
      <td>
        {if in_array('TwoFactorProviderBackupCodes', $enabled_providers)}
          {admin_icon title='Enabled' icon='true.gif'}
        {else}
          {admin_icon title='Disabled' icon='false.gif'}
        {/if}
      </td>
      <td>
        <a href="{cms_action_url action='setup_backup_codes'}">{admin_icon icon='edit.gif'}</a>
      </td>
    </tr>
  </tbody>
</table>
