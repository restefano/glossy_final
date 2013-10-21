<?php 


class FrontController
{
	public $errors = array();
	public $ssl = false;
	protected $template;
	
	public function __construct()
	{
		global $useSSL;
		$useSSL = $this->ssl;
	}
	
	public function setMedia()
	{
		
	}
	
	public function preProcess()
	{
		
	}

	public function run()
	{
		global $smarty;
		/* JS files call */
		$js_files = array(__PS_BASE_URI__.'js/jquery/jquery.scrollto.js', _THEME_JS_DIR_.'history.js');
		
		include(dirname(__FILE__).'/../../../header.php');
		$this->preProcess();
		// var_dump($this->template);
		$smarty->display($this->template);
		include(dirname(__FILE__).'/../../../footer.php');

	}
}