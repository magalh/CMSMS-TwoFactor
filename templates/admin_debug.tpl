<h4>Debug Info</h4>
<p><strong>$is_pro_installed:</strong> {if $is_pro_installed}true{else}false{/if}</p>
<p><strong>$is_pro_active:</strong> {if $is_pro_active}true{else}false{/if}</p>
{if $is_pro_installed && $mod_pro}
<p><strong>$mod_pro->IsProEnabled():</strong> {if $mod_pro->IsProEnabled()}true{else}false{/if}</p>
{/if}

<h4>Basic Preferences (TwoFactor)</h4>
<table class="pagetable">
  <thead>
    <tr>
      <th>Preference</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    {foreach $basic_prefs as $pref}
    <tr>
      <td>{$pref.name|escape}</td>
      <td>{$pref.value|escape}</td>
    </tr>
    {/foreach}
  </tbody>
</table>

{if $mod_pro}
<h4>Pro Preferences (TwoFactorPro)</h4>
<table class="pagetable">
  <thead>
    <tr>
      <th>Preference</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    {foreach $pro_prefs as $pref}
    <tr>
      <td>{$pref.name|escape}</td>
      <td>{$pref.value|escape}</td>
    </tr>
    {/foreach}
  </tbody>
</table>
{/if}

<p><a href="{$clear_url}" onclick="return confirm('Are you sure you want to clear all preferences?');">Clear All Preferences</a></p>
