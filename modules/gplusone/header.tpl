<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
  {ldelim}lang: '{$gpo_lang_code}}'{rdelim}
</script>

{if $gpo_cover && $gpo_cover != ''}
<!-- Update your html tag to include the itemscope and itemtype attributes. -->
<html itemscope itemtype="http://schema.org/Product">

<!-- Add the following three tags inside head. -->
<meta itemprop="image" content="{$gpo_cover}">
{/if}