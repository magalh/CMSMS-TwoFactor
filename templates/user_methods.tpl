{if $is_2fa_active}
  <div class="information">
    <p>{$mod->Lang('two_factor_active')}</p>
  </div>
  {else}
  <div class="warning">
    <p>{$mod->Lang('two_factor_inactive')}</p>
  </div>
{/if}
{if $enabled_providers}
  <p class="pagetext">{$mod->Lang('primary_method_info')}:</p>
  <form method="post" action="{cms_action_url action='user_prefs' active_tab='methods'}" style="margin-bottom:20px;">
    <div class="pageoverflow">
      
      <p class="pageinput">
        <select name="{$actionid}set_primary" id="primary_method">
          <option value="disabled"{if $primary_provider == 'disabled'} selected{/if}>{$mod->Lang('disabled')}</option>
          {foreach $providers as $key => $provider}
            {if in_array($key, $enabled_providers) && $key != 'TwoFactorProviderBackupCodes'}
              {if $key == 'TwoFactorProviderSMS' && !$sms_available}
                {* Skip SMS if not available *}
              {else}
                <option value="{$key}"{if $key == $primary_provider} selected{/if}>{$provider->get_label()}</option>
              {/if}
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
    {if $sms_available}
    <tr>
    {else}
    <tr style="opacity: 0.5;">
    {/if}
      <td class="p_top_10 p_bottom_10"><strong>{$mod->Lang('provider_sms')}</strong><br/><small>{$mod->Lang('sms_description')}</small></td>
      <td>
        {if in_array('TwoFactorProviderSMS', $enabled_providers)}
          {admin_icon title='Enabled' icon='true.gif'}
        {else}
          {admin_icon title='Disabled' icon='false.gif'}
        {/if}
      </td>
      <td>
        {if $sms_available}
          {if $smscredit_enabled == '1'}
            <a href="{cms_action_url action='setup_sms_credit_enabled'}">{admin_icon icon='edit.gif'}</a>
          {else}
            <a href="{cms_action_url action='setup_sms'}">{admin_icon icon='edit.gif'}</a>
          {/if}
        {else}
          <span style="color: #999;"></span>
        {/if}
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
