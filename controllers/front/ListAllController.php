<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ListAllControllerCore extends FrontController
{
	public $php_self = 'list-all';

	public function initContent()
	{
		parent::initContent();

		$this->productSort();
		$nbProducts = Product::getAllProducts($this->context->language->id, 0, 0, true, $this->orderBy, $this->orderWay),
		$this->pagination($nbProducts);

		$this->context->smarty->assign(array(
			'products' => Product::getAllProducts($this->context->language->id, (int)($this->p) - 1, (int)($this->n), false, $this->orderBy, $this->orderWay),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'nbProducts' => (int)($nbProducts),
			'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
			'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM')

		));

		$this->setTemplate(_PS_THEME_DIR_.'list-all.tpl');
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addCSS(_THEME_CSS_DIR_.'product_list.css');

		if (Configuration::get('PS_COMPARATOR_MAX_ITEM'))
			$this->addJS(_THEME_JS_DIR_.'products-comparison.js');
	}
}

