{if $status == 'ok'}
	<p>{l s='Sua compra está finalizada. Obrigado por comprar conosco!' sprintf=$shop_name mod='pagseguro'}
		<br /><br />{l s='Sua compra ficou num total de:' mod='pagseguro'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br /><br />{l s='Foi enviado um e-mail para você com as informações dessa compra.' mod='pagseguro'}
		<br /><br /><strong>{l s='Seu pedido será processado assim que recebermos a confirmação de pagamento. Você pode acompanhá-lo através da seção "Minha GlossyMe"' mod='pagseguro'}</strong>
		<br /><br />{l s='Em caso de dúvidas, entre em contato conosco clicando} <a href="{$link->getPageLink('contact', true)}">{l s='aqui' mod='pagseguro'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='Encontramos um problema com sua compra. Caso julgue ser um erro, por favor contate-nos' mod='pagseguro'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='suporte ao consumidor' mod='pagseguro'}</a>.
	</p>
{/if}
