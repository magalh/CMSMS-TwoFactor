<div class="pagecontainer">
    <h3>SMS Verification Logs</h3>
    
    {if $error}
        <div class="pageerrorcontainer">
            <p class="pageerror">{$error}</p>
        </div>
    {/if}
    
    {if $logs && count($logs) > 0}
        <table class="pagetable">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Phone Number</th>
                    <th>Country</th>
                    <th>Status</th>
                    <th>Credits Used</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                {foreach $logs as $log}
                    <tr>
                        <td>{$log.timestamp|cms_date_format}</td>
                        <td>{$log.phone|default:'-'}</td>
                        <td>{$log.country|default:'-'}</td>
                        <td>
                            {if $log.status == 'success' || $log.status == 'approved'}
                                <span style="color: green;">✓ Success</span>
                            {elseif $log.status == 'failed'}
                                <span style="color: red;">✗ Failed</span>
                            {else}
                                {$log.status|default:'-'}
                            {/if}
                        </td>
                        <td>{$log.amount|default:'0'}</td>
                        <td>{$log.type|default:'-'}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        
        {if $total_pages > 1}
            <div class="pageoptions" style="margin-top: 20px; text-align: center;">
                {if $page > 1}
                    <a href="{cms_action_url action=defaultadmin __activetab=verify_logs page=$page-1}" class="pageoption">&laquo; Previous</a>
                {/if}
                
                <span style="margin: 0 15px;">Page {$page} of {$total_pages} ({$total_logs} total logs)</span>
                
                {if $page < $total_pages}
                    <a href="{cms_action_url action=defaultadmin __activetab=verify_logs page=$page+1}" class="pageoption">Next &raquo;</a>
                {/if}
            </div>
        {/if}
    {else}
        <p class="information">No verification logs found.</p>
    {/if}
</div>
