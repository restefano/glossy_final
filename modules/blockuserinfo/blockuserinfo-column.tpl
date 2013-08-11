{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Block user information module HEADER -->


<div id="block_header_user" class="block">
	<h4 class="title_block" style="font-weight:normal">
	
		{if !$logged}
			Minha GlossyMe
		{else}
			<a href="{$link->getPageLink('my-account', true)}" title="{l s='Ver minha conta na GlossyMe'}" rel="nofollow">{l s='Minha GlossyMe' }</a>		
		{/if}
	
	</h4>
<ul>
<div class="block_content">

		{if $logged}
			<p id="header_user_info">
			Olá, <a href="{$link->getPageLink('my-account', true)}" title="{l s='Ver minha conta' }" class="account" rel="nofollow"><span>{$cookie->customer_firstname} !</span></a>
			<a href="{$link->getPageLink('index', true, NULL, "mylogout")}" title="{l s='Sair da sua conta' }" class="logout" rel="nofollow">{l s='Sair' }</a>
			</p>
		{else}
			<p id="header_user_info">
				Olá !
			<a href="{$link->getPageLink('my-account', true)}" title="{l s='Quem é você ?' }" class="login" rel="nofollow">{l s='Entrar/Fazer Cadastro' }</a>
			</p>
		{/if}

<br>
<br>
<br>
	<ul id="header_nav">
		{if !$PS_CATALOG_MODE}
		<li id="shopping_cart" style="font-size:10px;">
			<a href="{$link->getPageLink($order_process, true)}" title="{l s='Ver meu carrinho'}" rel="nofollow">{l s='Total:'}
			<span class="ajax_cart_quantity{if $cart_qties == 0} hidden{/if}">{$cart_qties}</span>
			<span class="ajax_cart_product_txt{if $cart_qties != 1} hidden{/if}">{l s='produto' mod='blockuserinfo'}</span>
			<span class="ajax_cart_product_txt_s{if $cart_qties < 2} hidden{/if}">{l s='produtos' mod='blockuserinfo'}</span>
			<span class="ajax_cart_total{if $cart_qties == 0} hidden{/if}">
				{if $cart_qties > 0}
					{if $priceDisplay == 1}
						{assign var='blockuser_cart_flag' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
						{convertPrice price=$cart->getOrderTotal(false, $blockuser_cart_flag)}
					{else}
						{assign var='blockuser_cart_flag' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
						{convertPrice price=$cart->getOrderTotal(true, $blockuser_cart_flag)}
					{/if}
				{/if}
			</span>
			<span class="ajax_cart_no_product{if $cart_qties > 0} hidden{/if}"> {l s='(vazio)'}</span>
			</a>
		</li>

		{/if}
	</ul>

</div>
</ul>

</div>
<!-- /Block user information module HEADER -->