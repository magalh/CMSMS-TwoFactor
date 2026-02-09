<h3>{$mod->Lang('user_management')}</h3>

<p>{$mod->Lang('user_management_help')}</p>

<table class="pagetable">
    <thead>
        <tr>
            <th>{$mod->Lang('username')}</th>
            <th>{$mod->Lang('email')}</th>
            <th>{$mod->Lang('2fa_status')}</th>
            <th>{$mod->Lang('enabled_methods')}</th>
            <th>{$mod->Lang('failed_attempts')}</th>
            <th>{$mod->Lang('trusted_devices')}</th>
            <th>{$mod->Lang('actions')}</th>
        </tr>
    </thead>
    <tbody>
        {foreach $users as $user}
        <tr{if $user.is_locked} class="row2" style="background-color: #ffe6e6;"{/if}>
            <td>
                {$user.username}
                {if $user.is_locked}
                    <span style="color: red; font-weight: bold;"> (LOCKED)</span>
                {/if}
            </td>
            <td>{$user.email}</td>
            <td>
                {if $user.has_2fa}
                    {admin_icon title='Enabled' icon='true.gif'} {$mod->Lang('enabled')}
                {else}
                    {admin_icon title='Disabled' icon='false.gif'} {$mod->Lang('disabled')}
                {/if}
            </td>
            <td>
                {if $user.enabled_providers}
                    {', '|implode:$user.enabled_providers}
                {else}
                    -
                {/if}
            </td>
            <td>
                {if $user.failed_attempts > 0}
                    <span style="color: {if $user.failed_attempts >= 5}red{elseif $user.failed_attempts >= 3}orange{else}black{/if}; font-weight: bold;">
                        {$user.failed_attempts}
                    </span>
                {else}
                    0
                {/if}
            </td>
            <td>{$user.trusted_devices}</td>
            <td>
                {if $user.has_2fa}
                    <a href="{cms_action_url action='defaultadmin' active_tab='user_management' disable_2fa_user=$user.id}" 
                       onclick="return confirm('{$mod->Lang('confirm_disable_user_2fa')|replace:'%s':$user.username}');">
                        {$mod->Lang('disable_2fa')}
                    </a>
                {else}
                    -
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<div class="information" style="margin-top: 20px;">
    <p><strong>{$mod->Lang('legend')}:</strong></p>
    <ul>
        <li>{$mod->Lang('legend_locked')}</li>
        <li>{$mod->Lang('legend_failed_attempts')}</li>
        <li>{$mod->Lang('legend_disable')}</li>
    </ul>
</div>
