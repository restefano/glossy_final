<script type="text/javascript">
function track_plusone(gpovote) {ldelim}
_gaq.push(['_trackEvent', 'Social Shares', 'Google +1 Vote', gpovote.href]);
{rdelim}
</script>
{if $gpo_default_hook}
<li>
{else}
{* If you're using a custom hook, you can customize
 the following div with the style you want.
 If the share / send box appear cut off, add position: absolute to the style*}
<div class="gplusone_container">
{/if}
	<g:plusone size="{$gpo_layout}" count="{if $gpo_count == '1'}true{else}false{/if}"></g:plusone>
{if $gpo_default_hook}
</li>
{else}
</div>
{/if}