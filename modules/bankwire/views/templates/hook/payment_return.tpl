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

{if $status == 'ok'}
<p>Parabéns ! Sua compra na GlossyMe foi confirmada !
		<br /><br />
		Você deve realizar um depósito no valor de <span class="price"> <strong>{$total_to_pay}</strong></span> em nome de <strong>{$bankwireOwner}</strong>, proprietária da GlossyMe em uma das contas abaixo:
		<br /><br /><strong>{$bankwireDetails}</strong>
		<br /><br />Um e-mail foi enviado para você contendo essas informações.
		<br /><br />Após realizar o depósito envie um e-mail com o comprovante para <span style="color:blue">contato@glossyme.com.br</span> 
		<br /><br />Seu pedido será processado imediatamente após o recebimento do comprovante de depósito.</strong>
		<br /><br />Em caso de dúvidas, entre em contato conosco através da seção <a href="{$link->getPageLink('contact', true)}" style="color:blue">Fale com a GlossyMe</a>.
	</p>
{else}
	<p class="warning">
		Encontramos um problema em sua compra. Por favor entre em contato conosco através da seção <a href="{$link->getPageLink('contact', true)}" style="color:blue">Fale com a GlossyMe</a>.
	</p>
{/if}
