<?php


class ModuleFrontControllerCore extends FrontController
{
	/**
	 * @var Module
	 */
	public $module;
	public $context;

	/** 
	 * for Frontcontroler presta 1.4 
	 */
	public function preProcess() {
		$this->setMedia();
		$this->postProcess();	
	}

	public function __construct($moduleName)
	{
		$this->controller_type = 'modulefront';
		$this->context = PrestoChangeoContext::getContext();
		$this->module = Module::getInstanceByName($moduleName);

		if (!$this->module->active)
			Tools::redirect('index');
		// $this->page_name = 'module-'.$this->module->name.'-'.Dispatcher::getInstance()->getController();
		parent::__construct();
	}

	/**
	 * Assign module template
	 *
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		if (floatval(substr(_PS_VERSION_,0,3)) > 1.3) {
			if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template;
			elseif (Tools::file_exists_cache($this->getTemplatePath().$template))
				$this->template = $this->getTemplatePath().$template;
			else
				throw new PrestaShopException("Template '$template'' not found");
		} else {
			if (file_exists(_PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template;
			elseif (file_exists($this->getTemplatePath().$template))
				$this->template = $this->getTemplatePath().$template;
			else
				throw new Exception("Template '$template'' not found");
		}
		
	}

	public function displayContent()
	{
		self::$smarty->display($this->template);
	}

	/**
	 * Get path to front office templates for the module
	 *
	 * @return string
	 */
	public function getTemplatePath()
	{
		return _PS_MODULE_DIR_.$this->module->name.'/views/templates/front/';
	}
}

class ModuleFrontController extends ModuleFrontControllerCore {}
