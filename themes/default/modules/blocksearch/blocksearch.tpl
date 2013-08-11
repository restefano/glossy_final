{*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Block search module ATUALIZADO -->
<div id="search_block_left" class="block exclusive" style="margin-bottom:0px;">
	<p class="title_block">{l s='Search' mod='blocksearch'}</p>
	<form method="get" action="{$link->getPageLink('search', true)}" id="searchbox">
		<p class="block_content" style="margin-top: 10px; padding-bottom: 0px;">
			<label for="search_query_block" style="margin-left:10px;">{l s='Enter a product name' mod='blocksearch'}</label>
			<input type="hidden" name="orderby" value="position" />
			<input type="hidden" name="controller" value="search" />
			<input type="hidden" name="orderway" value="desc" />
			<center>
			<input class="search_query" style="margin-top:5px; width:150px; height:25px;" type="text" id="search_query_block" name="search_query" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|htmlentities:$ENT_QUOTES:'utf-8'|stripslashes}{/if}" />
			<input type="submit" style="margin-top:10px;" id="search_button" class="button_mini" value="Encontrar na Loja" /> 
			</center>
		</p>
	</form>
</div>
{include file="$self/blocksearch-instantsearch.tpl"}
<!-- /Block search module -->
