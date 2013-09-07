<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class ShowAllProducts extends Module
{
 
	public function __construct()
  	{
    	$this->name = 'showallproducts';
    	$this->tab = 'front_office_features';
    	$this->version = '1.0';
    	$this->author = 'Ricardo Estefano Rosa';
    	$this->need_instance = 0;
    	$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.5'); 
    	$this->dependencies = array('blockcart');
 
    	parent::__construct();
 
    	$this->displayName = $this->l('Show All Products');
    	$this->description = $this->l('Show all products on a single page.');
 
    	$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
    	if (!Configuration::get('MYMODULE_NAME'))       
      	$this->warning = $this->l('No name provided');
  	}

	public function install()
	{
		if (Shop::isFeatureActive())
    		Shop::setContext(Shop::CONTEXT_ALL);
 
  		return 	parent::install() &&
			    $this->registerHook('header') &&
			    $this->registerHook('displayHome') &&
    			Configuration::updateValue('RANDOM', 'true');
  	}	

  	public function uninstall()
	{
  		return parent::uninstall() && Configuration::deleteByName('RANDOM');
	}

	public function hookDisplayHeader()
	{
  		$this->context->controller->addCSS($this->_path.'css/showallproducst.css', 'all');
	} 

	public function hookHome($params)
	{
  		return $this->display(__FILE__, 'showallproducts.tpl');
	}


}
?>