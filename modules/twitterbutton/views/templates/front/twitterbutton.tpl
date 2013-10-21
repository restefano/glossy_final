{if $tw_default_hook}
<li>
{else}
{* If you're using a custom hook, you can customize
 the following div with the style you want.
 If the share / send box appear cut off, add position: absolute to the style*}
<div class="twitter_container">
{/if}

    {if $tw_button == 'share'}
        <a href="https://twitter.com/share" class="twitter-share-button" data-text="{$tw_text}" data-via="{$tw_by}" data-lang="{$tw_lang}" {if $tw_button_size == 'large'}data-size="large"{/if} {if $tw_count == 0}data-count="none"{/if} {if $tw_tag}data-hashtags="{$tw_tag}"{/if} >Tweet</a>
    {elseif $tw_button == 'follow'}
        <a href="https://twitter.com/{$tw_by}" class="twitter-follow-button" {if $tw_count == 0}data-show-count="false"{else}data-show-count="true"{/if} data-lang="{$tw_lang}" {if $tw_button_size == 'large'}data-size="large"{/if} >Follow @{$tw_by}</a>
    {elseif $tw_button == 'tag'}
        <a href="https://twitter.com/intent/tweet?button_hashtag={$tw_tag}&text={$tw_text}" data-lang="{$tw_lang}" class="twitter-hashtag-button" {if $tw_button_size == 'large'}data-size="large"{/if} >Tweet #{$tw_tag}</a>
    {elseif $tw_button == 'mention'}
        <a href="https://twitter.com/intent/tweet?screen_name={$tw_by}" class="twitter-mention-button" {if $tw_button_size == 'large'}data-size="large"{/if}>Tweet to @{$tw_by}</a>
    {/if}
    
    <script>
        !function(d,s,id){
            var js,fjs=d.getElementsByTagName(s)[0];
            if(!d.getElementById(id)){
                js=d.createElement(s);
                js.id=id;
                js.src="//platform.twitter.com/widgets.js";
                fjs.parentNode.insertBefore(js,fjs);
            }
        }
        (document,"script","twitter-wjs");
    </script>
 
{if $tw_default_hook}
</li>
{else}
</div>
{/if}