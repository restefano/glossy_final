<?php

class testmoduleAllproductsModuleFrontController extends ModuleFrontController
{

	public function init()
	{
		$this->page_name = 'allproducts'; // page_name and body id
	    parent::init();
	}

	public function initContent()
	{
    	parent::initContent();
    	$products_partial = Product::getProducts($this->context->language->id, 0, 30, 'name', 'asc');
    	$products = Product::getProductsProperties($this->context->language->id, $products_partial);
 

    	$this->context->smarty->assign(array(
        'products' => $products,
        'homeSize' => Image::getSize('home_default')
    	));

    	$this->setTemplate('allproducts.tpl');

	}   	

 
}

?>