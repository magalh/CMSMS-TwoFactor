<h3>{$mod->Lang('trusted_devices')}</h3>

{if count($devices) > 0}
<table class="pagetable">
    <thead>
        <tr>
            <th>{$mod->Lang('device_name')}</th>
            <th>{$mod->Lang('ip_address')}</th>
            <th>{$mod->Lang('device_added')}</th>
            <th>{$mod->Lang('device_expires')}</th>
            <th>{$mod->Lang('actions')}</th>
        </tr>
    </thead>
    <tbody>
        {foreach $devices as $device}
        <tr>
            <td>{$device.device_name|default:'Unknown Device'}</td>
            <td>{$device.ip_address|default:'Unknown'}</td>
            <td>{$device.created_at|date_format:'%Y-%m-%d %H:%M'}</td>
            <td>{$device.expires_at|date_format:'%Y-%m-%d %H:%M'}</td>
            <td>
                <a href="{cms_action_url action='user_prefs' active_tab='trusted_devices' revoke_device=$device.id}" 
                   onclick="return confirm('{$mod->Lang('confirm_revoke_device')}');">
                    {$mod->Lang('revoke')}
                </a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{else}
<p>{$mod->Lang('no_trusted_devices')}</p>
{/if}

<p class="pageinput" style="margin-top: 20px;">
    <em>{$mod->Lang('trusted_devices_help')}</em>
</p>
