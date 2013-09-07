<?php

class testmoduleAllproductsModuleFrontController extends ModuleFrontController
{

	public function init()
	{
		$this->page_name = 'ver_todos'; // page_name and body id
	    parent::init();
	}

	public function initContent()
	{
    	parent::initContent();
	    echo'LELE';
	}   	

 
}

?>