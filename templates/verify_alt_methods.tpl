{* TwoFactor: shared "alternative methods" block for verification pages.
   Expects: $alt_methods (each with .label, .url, .slug), $has_backup_codes,
   $using_backup, $backup_url, $primary_url, $mod. *}
{if !empty($alt_methods) || ($has_backup_codes && !$using_backup) || $using_backup}
<div class="tfa-alt">
	<span class="tfa-alt__label">{$mod->Lang('use_other_method')}</span>
	<div class="tfa-alt__list">
		{foreach $alt_methods as $alt}
			<a class="tfa-method" href="{$alt.url}">
				<span class="tfa-method__icon">
					{if $alt.slug == 'totp'}
						<svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="9" y1="18" x2="15" y2="18"/></svg>
					{elseif $alt.slug == 'email'}
						<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
					{elseif $alt.slug == 'sms'}
						<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
					{elseif $alt.slug == 'passkey'}
						<svg viewBox="0 0 24 24"><circle cx="10" cy="8" r="4"/><path d="M10.5 12A6 6 0 0 0 5 18v2h7"/><circle cx="18" cy="15" r="2"/><path d="M18 17v4l1.5-1.5M18 17l-1.5-1.5"/></svg>
					{elseif $alt.slug == 'security-key'}
						<svg viewBox="0 0 24 24"><circle cx="8" cy="12" r="4"/><path d="M12 12h9M18 12v4M15 12v3"/></svg>
					{else}
						<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.8.4-1 .9-1 1.7"/><line x1="12" y1="17" x2="12" y2="17"/></svg>
					{/if}
				</span>
				<span class="tfa-method__label">{$alt.label}</span>
				<span class="tfa-method__chevron">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
				</span>
			</a>
		{/foreach}

		{if $has_backup_codes && !$using_backup}
			<a class="tfa-method tfa-method--backup" href="{$backup_url}">
				<span class="tfa-method__icon">
					<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="9" x2="17" y2="9"/><line x1="7" y1="13" x2="17" y2="13"/><line x1="7" y1="17" x2="13" y2="17"/></svg>
				</span>
				<span class="tfa-method__label">{$mod->Lang('use_backup_code')}</span>
				<span class="tfa-method__chevron">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
				</span>
			</a>
		{/if}

		{if $using_backup && isset($primary_url)}
			<a class="tfa-method" href="{$primary_url}">
				<span class="tfa-method__icon">
					<svg viewBox="0 0 24 24"><path d="M9 14l-4-4 4-4"/><path d="M5 10h11a4 4 0 0 1 0 8h-1"/></svg>
				</span>
				<span class="tfa-method__label">{$mod->Lang('back_to_primary')}</span>
				<span class="tfa-method__chevron">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
				</span>
			</a>
		{/if}
	</div>
</div>
{/if}
