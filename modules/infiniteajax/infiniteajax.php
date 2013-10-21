<?php

if (!defined('_PS_VERSION_'))
  exit;

class infiniteajax extends Module
{
	function __construct()
	{
		$this->name = 'infiniteajax';
		$this->tab = 'front_office_features';
		$this->version = 1.2;
                $this->author = 'Mellow (PrestaShop Forum Member)';
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Infinite Ajax Scroll');
		$this->description = $this->l('Infinite scroll with automatic pagination');
 
		parent::__construct();
	}
	
	function install()
	{
		if (!parent::install()
		|| !$this->registerHook('header'))
			return false;
		Configuration::updateValue('PS_INFINITE_AJAX_TRESHOLD', 0);
		Configuration::updateValue('PS_INFINITE_AJAX_HISTORY', 1);
		Configuration::updateValue('PS_INFINITE_AJAX_BLKLAYRD', 0);
		Configuration::updateValue('PS_INFINITE_AJAX_LOADING', '');
		Configuration::updateValue('PS_INFINITE_AJAX_TRIGGERPAGE', '');
		Configuration::updateValue('PS_INFINITE_AJAX_TRIGGER', '');
		Configuration::updateValue('PS_INFINITE_AJAX_NONELEFT', '');
		Configuration::updateValue('PS_INFINITE_AJAX_NONELEFTLINK', 0);
		Configuration::updateValue('PS_INFINITE_AJAX_LOADER', 'modules/infiniteajax/images/loader.gif');
		Configuration::updateValue('PS_INFINITE_AJAX_PRODLISTELE', 'product_list,ajax_block_product,pagination,pagination_next');
		Configuration::updateValue('PS_INFINITE_AJAX_ONRENDERCOMP', 'function(){if(typeof(ajaxCart)!=\'undefined\'){ajaxCart.overrideButtonsInThePage();ajaxCart.refresh()}if(typeof(reloadProductComparison)==\'function\')reloadProductComparison()}');
                return true;
	}

	function uninstall()
	{
		Configuration::deleteByName('PS_INFINITE_AJAX_TRESHOLD');
		Configuration::deleteByName('PS_INFINITE_AJAX_HISTORY');
		Configuration::deleteByName('PS_INFINITE_AJAX_BLKLAYRD');
		Configuration::deleteByName('PS_INFINITE_AJAX_LOADING');
		Configuration::deleteByName('PS_INFINITE_AJAX_TRIGGERPAGE');
		Configuration::deleteByName('PS_INFINITE_AJAX_TRIGGER');
		Configuration::deleteByName('PS_INFINITE_AJAX_NONELEFT');
		Configuration::deleteByName('PS_INFINITE_AJAX_NONELEFTLINK');
		Configuration::deleteByName('PS_INFINITE_AJAX_LOADER');
		Configuration::deleteByName('PS_INFINITE_AJAX_PRODLISTELE');
		Configuration::deleteByName('PS_INFINITE_AJAX_ONRENDERCOMP');
		if (!parent::uninstall()
		|| !$this->unregisterHook('header'))
			return false;
		return true;
	}

	public function getLoaders()
	{
		$thedir = dirname(__FILE__).'/images/';
		$dirimages = opendir($thedir);
		while ($isitem = readdir($dirimages)) {
			$theimages[] = $isitem;
		}
		sort($theimages);
		foreach ($theimages as $theimage)
			if ($theimage != '.' && $theimage != '..' && $theimage != 'Thumbs.db' && $theimage != 'index.php' && $theimage != 'icon' && !is_dir($theimage))
				$loaders[] = $theimage;
		return $loaders;
	}

	public function isPhoneDevice()
	{
		if (version_compare(_PS_VERSION_,'1.5','>'))
			require_once(_PS_TOOL_DIR_.'mobile_Detect/Mobile_Detect.php');
		else
			require_once('Mobile_Detect.php');
		$this->mobile_detect = new Mobile_Detect();
		$this->phone_device = false;
		if ($this->mobile_detect->isMobile() && !$this->mobile_detect->isTablet())
			$this->phone_device = true;
		
		// * Debug *
		//if ($this->phone_device == true) echo "This is a Phone or a SmartPhone";
		//if ($this->mobile_detect->isTablet()) echo "This is a Tablet";
		
		return $this->phone_device;
	}

	function hookheader($params)
	{
		global $smarty, $cookie;



		if ($this->isPhoneDevice()) return;
		
		if (version_compare(_PS_VERSION_,'1.5','>')) {
			$page_name = Tools::getValue('controller');
			$id_shop = (int) Context::getContext()->shop->id;
			$pages_with_productlist = array("category", "manufacturer", "supplier", "search", "pricesdrop", "newproducts", "bestsales", "listall");
		} else {
			$pathinfo = pathinfo(__FILE__);
			$page_name = basename($_SERVER['PHP_SELF'], '.'.$pathinfo['extension']);
			$id_shop = 0;
			$pages_with_productlist = array("category", "manufacturer", "supplier", "search", "prices-drop", "new-products", "best-sales", "listall");
		}

		// Si il s'agit d'une page affichant une liste de produits
		if (in_array($page_name, $pages_with_productlist)) {

			if ($page_name == "manufacturer" AND !((int)Tools::getValue('id_manufacturer') > 0)) return;
			if ($page_name == "supplier" AND !((int)Tools::getValue('id_supplier') > 0)) return;

			$blocklayered_activated = false;
			$isactive_blocklayered = false;
			if ($page_name == "category") {
				$id_category = (int)Tools::getValue('id_category');

				if (version_compare(_PS_VERSION_,'1.5','>'))
					$blocklayered_activated = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue("SELECT m.id_module FROM "._DB_PREFIX_."module m, "._DB_PREFIX_."module_shop s WHERE m.name = 'blocklayered' AND m.active = 1 AND m.id_module = s.id_module AND s.id_shop = ".$id_shop);
				else
					$blocklayered_activated = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue("SELECT id_module FROM "._DB_PREFIX_."module WHERE name = 'blocklayered' AND active = 1");

                                if ($blocklayered_activated == true)
					$isactive_blocklayered = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_category FROM '._DB_PREFIX_.'layered_category WHERE id_category = '.$id_category.' AND id_shop = '.$id_shop);
				
				// Si il s'agit d'une catégorie avec navigation à facettes, et que l'option est désactivée dans le module
				if ($isactive_blocklayered == true AND Configuration::get('PS_INFINITE_AJAX_BLKLAYRD') == 0)
					return;
			}

			if (version_compare(_PS_VERSION_,'1.5','>')) {
				$this->context->controller->addJS(($this->_path).'jquery-ias.js');
				$this->context->controller->addCSS(($this->_path).'css/jquery.ias.css', 'all');
			} else {
				Tools::addJS(($this->_path).'jquery-ias.js');
				Tools::addCSS(($this->_path).'css/jquery.ias.css', 'all');
			}
			
			$ListElements = explode(',', Configuration::get('PS_INFINITE_AJAX_PRODLISTELE'));
			$tresholdMargin = Configuration::get('PS_INFINITE_AJAX_TRESHOLD');
			$triggerPageThreshold = Configuration::get('PS_INFINITE_AJAX_TRIGGERPAGE');
			$trigger = Configuration::get('PS_INFINITE_AJAX_TRIGGER', (int)$cookie->id_lang);
			$noneleft = Configuration::get('PS_INFINITE_AJAX_NONELEFT', (int)$cookie->id_lang);
			$loader = Configuration::get('PS_INFINITE_AJAX_LOADER');
			$loadingtext = Configuration::get('PS_INFINITE_AJAX_LOADING', (int)$cookie->id_lang);
			$RenderCompleteFunction = Configuration::get('PS_INFINITE_AJAX_ONRENDERCOMP');

			return "
			<!-- infinite-ajax-scroll -->
			<script type=\"text/javascript\">
			// <![CDATA[
			
			$(document).ajaxStart(function() { jQuery.ajaxRunning = true; }).ajaxStop(function() { jQuery.ajaxRunning = false; });
			
			$(document).ready(function(){
				jQuery.ias({
					container: '#".($ListElements[0] ? $ListElements[0] : 'product_list')."',
					item: '.".($ListElements[1] ? $ListElements[1] : 'ajax_block_product')."',
					pagination: '#".($ListElements[2] ? $ListElements[2] : 'pagination')."',
					next: '#".($ListElements[3] ? $ListElements[3] : 'pagination_next')." a',
					thresholdMargin: ".($tresholdMargin != '' ? $tresholdMargin : '0').",
					triggerPageThreshold: ".($triggerPageThreshold != '' ? $triggerPageThreshold : '1000').",
					trigger: '".($trigger != '' ? addslashes($trigger) : 'Afficher plus de produits')."',
					noneleft: '".($noneleft != '' ? addslashes($noneleft) : '')."',
					noneleftlink: ".(Configuration::get('PS_INFINITE_AJAX_NONELEFTLINK') == 1 ? 'true' : 'false').",
					history: ".(Configuration::get('PS_INFINITE_AJAX_HISTORY') == 1 ? 'true' : 'false').",
					onRenderComplete: ".($RenderCompleteFunction ? $RenderCompleteFunction : 'function(){}').",
					LayeredNavCat: ".($isactive_blocklayered == true ? 'true' : 'false').",
					loadingtext: '".($loadingtext != '' ? addslashes($loadingtext) : '')."',
					loader: '".($loader != '' ? '<img src="'.__PS_BASE_URI__.$loader.'"/>' : '')."'
				});
			});
			//]]>
			</script>
			<!-- /infinite-ajax-scroll -->
			";
		}
		return;
	}

	public function getContent() {
		$this->_html = '<h2>'.$this->l('Settings').' : '.$this->displayName.'</h2>';
		$errors = '';

		if (Tools::isSubmit('submitConfig')) {

		$languages = Language::getLanguages();
		$loadtext = array();
		$triggertext = array();
		$nonelefttext = array();
			foreach ($languages AS $language) {
				$loadtext[$language['id_lang']] = $_POST['loadtxt_'.$language['id_lang']];
				$triggertext[$language['id_lang']] = $_POST['trigtxt_'.$language['id_lang']];
				$nonelefttext[$language['id_lang']] = $_POST['nonetxt_'.$language['id_lang']];
			}
			$PS_INFINITE_AJAX_PRODLISTELE =	Tools::getValue('PRODLSTID').','.Tools::getValue('PRODCLASS').','.Tools::getValue('PRODPAGID').','.Tools::getValue('PRODNEXID');
			
			if (!Configuration::updateValue('PS_INFINITE_AJAX_TRESHOLD', Tools::getValue('PS_INFINITE_AJAX_TRESHOLD')))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_TRIGGERPAGE', Tools::getValue('PS_INFINITE_AJAX_TRIGGERPAGE')))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_HISTORY', Tools::getValue('PS_INFINITE_AJAX_HISTORY')))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_NONELEFTLINK', Tools::getValue('PS_INFINITE_AJAX_NONELEFTLINK')))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_BLKLAYRD', Tools::getValue('PS_INFINITE_AJAX_BLKLAYRD')))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_LOADING', $loadtext))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_TRIGGER', $triggertext))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_NONELEFT', $nonelefttext))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_LOADER', Tools::getValue('PS_INFINITE_AJAX_LOADER')))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_PRODLISTELE', $PS_INFINITE_AJAX_PRODLISTELE))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			elseif (!Configuration::updateValue('PS_INFINITE_AJAX_ONRENDERCOMP', str_replace(array("\r\n", "\r", "\n"), ' ', Tools::getValue('PS_INFINITE_AJAX_ONRENDERCOMP'))))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			else
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="" />'.$this->l('Settings updated').'</div>';
		}

		$this->_displayForm();
		return $this->_html;
        }

	private function _displayForm() {
		global $cookie;
		/* Language */
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages(false);
		$divLangName = 'loadtxtÂ¤trigtxtÂ¤nonetxt';

		$ListElements = explode(',', Configuration::get('PS_INFINITE_AJAX_PRODLISTELE'));
		$this->_html .='
			<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Behavior :').'</legend>

					<label>'.$this->l('History').'</label>
					<div class="margin-form" style="padding-top:5px;">
						<input type="radio" name="PS_INFINITE_AJAX_HISTORY" id="PS_INFINITE_AJAX_HISTORY_on" value="1" '.(Tools::getValue('PS_INFINITE_AJAX_HISTORY', Configuration::get('PS_INFINITE_AJAX_HISTORY')) ? 'checked="checked" ' : '').'/>
						<label class="t" for="PS_INFINITE_AJAX_HISTORY_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" />&nbsp;&nbsp;&nbsp;</label>
						<input type="radio" name="PS_INFINITE_AJAX_HISTORY" id="PS_INFINITE_AJAX_HISTORY_off" value="0" '.(!Tools::getValue('PS_INFINITE_AJAX_HISTORY', Configuration::get('PS_INFINITE_AJAX_HISTORY')) ? 'checked="checked" ' : '').'/>
						<label class="t" for="PS_INFINITE_AJAX_HISTORY_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
						<p class="clear">'.$this->l('The history uses hashes (in the format : #/page-x) to remember the last viewed page. When a visitor hits the back button after visiting an item from that page, it will load all items up to that last page and scrolls it into view. The use of hashes can be problematic in some cases, in which case you can disable this feature.').'</p>
					</div>

					<label>'.$this->l('Treshold Margin').'</label>
				        <div class="margin-form">
					        <input type="text" name="PS_INFINITE_AJAX_TRESHOLD" value="'.Configuration::get('PS_INFINITE_AJAX_TRESHOLD').'" size="4" />
							<span> '.$this->l('pixels').'</span>
					        <p class="clear">'.$this->l('Setting a treshold margin of -N means that next page will start loading N pixel before the last item has scrolled into view. A positive margin means that next page will start loading N pixels after the last item.').'</p>
				        </div>

					<label>'.$this->l('Loader image').'</label>
				        <div class="margin-form">
					        <input type="text" name="PS_INFINITE_AJAX_LOADER" value="'.Configuration::get('PS_INFINITE_AJAX_LOADER').'" id="PS_INFINITE_AJAX_LOADER" size="90" />
					        <p class="clear">'.$this->l('Animated image to display when loading next page (relative to Prestashop instalation folder , default : modules/infiniteajax/images/loader.gif )').'</p>
                                                <p class="clear" style="padding:0px;margin:0px;"><ul style="padding:0px;margin:0px;">';

					foreach ( $this->getLoaders() as $id => $loader ) {
						$loaderimg = 'modules/infiniteajax/images/'.$loader;
						if (Configuration::get('PS_INFINITE_AJAX_LOADER') == $loaderimg) $check = 'checked = "checked"';
						else $check = '';
			
						$this->_html .= '
                                                <li style="padding:0px;display:inline-block;vertical-align:top;margin-right:20px;margin-bottom:10px;">
                                                <label style="width:auto;text-align:center;cursor:pointer;" title="'.$loader.'"><div style="height:20px;">
						<input type="radio" name="loadimg" value="'.$loaderimg.'" '.$check.' style="cursor:pointer;" onclick="$(\'#PS_INFINITE_AJAX_LOADER\').val(this.value);" ></div>
                                                <div><img src="../'.$loaderimg.'" style="padding:0px;" alt="'.$loader.'" /></div></label></li>';
					}

					$this->_html .= '</ul>
					</div>

					<label>'.$this->l('Loader text').'</label>
				        <div class="margin-form">';
					foreach ($languages as $language)
					$this->_html .= '
					<div id="loadtxt_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="loadtxt_'.$language['id_lang'].'" value="'.Configuration::get('PS_INFINITE_AJAX_LOADING', (int)$language['id_lang']).'" size="90" />
				        	<p class="clear">'.$this->l('Text to display when loading next page (default : nothing )').'</p>
					</div>';
					$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'loadtxt', true);
                                        $this->_html .= '
					<div class="clear"></div>
					</div>

					<label>'.$this->l('Auto loading limit').'</label>
				        <div class="margin-form">
					        <input type="text" name="PS_INFINITE_AJAX_TRIGGERPAGE" value="'.Configuration::get('PS_INFINITE_AJAX_TRIGGERPAGE').'" size="4" />
							<span> '.$this->l('pages').'</span>
					        <p class="clear">'.$this->l('Pages will be loaded automatically until this limit, then a link will be displayed. Users will have to manually trigger the loading of the next page by clicking this link. (default: none / disabled)').'</p>
				        </div>
					
					<label>'.$this->l('\'Load more\' link text').'</label>
				        <div class="margin-form">';
					foreach ($languages as $language)
					$this->_html .= '
					<div id="trigtxt_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="trigtxt_'.$language['id_lang'].'" value="'.Configuration::get('PS_INFINITE_AJAX_TRIGGER', (int)$language['id_lang']).'" size="90" />
				        	<p class="clear">'.$this->l('Default: empty - If empty and auto loading limit set, default will be \'Afficher plus de produits\'').'</p>
					</div>';
					$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'trigtxt', true);
                                        $this->_html .= '
					<div class="clear"></div>
					</div>

					<label>'.$this->l('\'Last page\' text').'</label>
				        <div class="margin-form">';
					foreach ($languages as $language)
					$this->_html .= '
					<div id="nonetxt_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="nonetxt_'.$language['id_lang'].'" value="'.Configuration::get('PS_INFINITE_AJAX_NONELEFT', (int)$language['id_lang']).'" size="90" />
				        	<p class="clear">'.$this->l('Text to display when the last page is reached (exemple: \'No more pages to load\' - default: none)').'</p>
					</div>';
					$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'nonetxt', true);
                                        $this->_html .= '
					<div class="clear"></div>
					</div>

					<label>'.$this->l('Link on \'Last page\' text').'</label>
					<div class="margin-form" style="padding-top:5px;">
						<input type="radio" name="PS_INFINITE_AJAX_NONELEFTLINK" id="PS_INFINITE_AJAX_NONELEFTLINK_on" value="1" '.(Tools::getValue('PS_INFINITE_AJAX_NONELEFTLINK', Configuration::get('PS_INFINITE_AJAX_NONELEFTLINK')) ? 'checked="checked" ' : '').'/>
						<label class="t" for="PS_INFINITE_AJAX_NONELEFTLINK_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" />&nbsp;&nbsp;&nbsp;</label>
						<input type="radio" name="PS_INFINITE_AJAX_NONELEFTLINK" id="PS_INFINITE_AJAX_NONELEFTLINK_off" value="0" '.(!Tools::getValue('PS_INFINITE_AJAX_NONELEFTLINK', Configuration::get('PS_INFINITE_AJAX_NONELEFTLINK')) ? 'checked="checked" ' : '').'/>
						<label class="t" for="PS_INFINITE_AJAX_NONELEFTLINK_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
						<p class="clear">'.$this->l('Add a \'Scroll to top\' link to \'Last page\' text (if set)').'</p>
					</div>

					<label>'.$this->l('Activate with Layered Nav').'</label>
					<div class="margin-form" style="padding-top:5px;">
						<input type="radio" name="PS_INFINITE_AJAX_BLKLAYRD" id="PS_INFINITE_AJAX_BLKLAYRD_on" value="1" '.(Tools::getValue('PS_INFINITE_AJAX_BLKLAYRD', Configuration::get('PS_INFINITE_AJAX_BLKLAYRD')) ? 'checked="checked" ' : '').'/>
						<label class="t" for="PS_INFINITE_AJAX_BLKLAYRD_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" />&nbsp;&nbsp;&nbsp;</label>
						<input type="radio" name="PS_INFINITE_AJAX_BLKLAYRD" id="PS_INFINITE_AJAX_BLKLAYRD_off" value="0" '.(!Tools::getValue('PS_INFINITE_AJAX_BLKLAYRD', Configuration::get('PS_INFINITE_AJAX_BLKLAYRD')) ? 'checked="checked" ' : '').'/>
						<label class="t" for="PS_INFINITE_AJAX_BLKLAYRD_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
						
<!--
                        <span style="font-size:14px;font-weight:bold;color:#fd2c11;">'.$this->l('( Experimental option! )').'</span>
						<p class="clear" style="font-weight:bold;color:#fd2c11;">'.$this->l('This module is incompatible with layered navigation module.').'<br />'
						.$this->l('You should activate this option only if you are working on this compatibility!').'</p>
//-->
						<p class="clear" style="font-weight:bold;font-size:12px;color:#fd2c11;">'.$this->l('Before activating this option, YOU MUST modify your file \'modules/blocklayered/blocklayered.js\'').'<br />'
						.$this->l('OR place the modified blocklayered.js file in folder \'themes/your-theme/js/modules/blocklayered/\'').'</p>
					
					</div>

				</fieldset><br />
				<fieldset>
					<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Product list elements (Configure according to your theme)').'</legend><br />

					<label>'.$this->l('Product list container id').'</label>
				        <div class="margin-form">
					        <input type="text" name="PRODLSTID" value="'.$ListElements[0].'" size="30" />
					        <p class="clear">'.$this->l('The id of the element containing the product list (in product-list.tpl) (default : \'product_list\')').'</p>
				        </div>

					<label>'.$this->l('Products container class').'</label>
				        <div class="margin-form">
					        <input type="text" name="PRODCLASS" value="'.$ListElements[1].'" size="30" />
					        <p class="clear">'.$this->l('The class of the product block elements (in product-list.tpl) (default : \'ajax_block_product\')').'</p>
				        </div>

					<label>'.$this->l('Pagination container id').'</label>
				        <div class="margin-form">
					        <input type="text" name="PRODPAGID" value="'.$ListElements[2].'" size="30" />
					        <p class="clear">'.$this->l('The id of the elements containing the pagination block (in pagination.tpl) (default : \'pagination\')').'</p>
				        </div>

					<label>'.$this->l('Next button id').'</label>
				        <div class="margin-form">
					        <input type="text" name="PRODNEXID" value="'.$ListElements[3].'" size="30" />
					        <p class="clear">'.$this->l('The id of the pagination \'Next\' (in pagination.tpl) (default : \'pagination_next\')').'</p>
				        </div>

				</fieldset><br />
				<fieldset>
					<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('CallBack function :').'</legend><br />

					<label>'.$this->l('\'onRenderComplete\' Function').'</label>
				        <div class="margin-form">
					        <textarea cols="100" rows="10" name="PS_INFINITE_AJAX_ONRENDERCOMP" id="PS_INFINITE_AJAX_ONRENDERCOMP" />'.Configuration::get('PS_INFINITE_AJAX_ONRENDERCOMP').'</textarea>
					        <p class="clear"><strong>'.$this->l('This javascript function will be executed at the end of loading a new page.').'</strong><br />'
					        .$this->l('Default : ').'function(){if(typeof(ajaxCart)!=\'undefined\'){ajaxCart.overrideButtonsInThePage();ajaxCart.refresh()}if(typeof(reloadProductComparison)==\'function\')reloadProductComparison()}</p>
					        <p class="clear">'.$this->l('This can be useful when you have a javascript function that normally performs some actions on the items using the "document.ready" event. When loading items from a new page using this module, the "document.ready" handler is not called. Use this event instead.').' Documentation : <a href="https://github.com/webcreate/Infinite-Ajax-Scroll" target="_blank" style="color:#0000ff;">github.com/webcreate/Infinite-Ajax-Scroll</a></p>
					        <p class="clear" style="font-weight:bold;">Nota : '
						.$this->l('It is best to compress your js function before inserting here. See').' <a href="http://javascriptcompressor.com/" target="_blank" style="color:#0000ff;">javascriptcompressor.com</a><br />
					        ex : function(){$(\'a.product_image\').hoverIntent(function(){$(this).parent(\'div.product_desc\').stop()},function(){return})}</p>
				        </div>
				</fieldset><br />

				<fieldset>
				        <div style="clear:both;">&nbsp;</div>
				        <div class="margin-form">
                                                <input type="submit" name="submitConfig" value="'.$this->l('   Save   ').'" class="button" />
                                        </div>
				</fieldset>
			</form>';

			$this->_html .= "
			<script type='text/javascript'>
			// <![CDATA[
			$(document).ready(function(){
				var delay = (function(){
					var timer = 0;
					return function(callback, ms){
						clearTimeout (timer);
						timer = setTimeout(callback, ms);
					};
				})();
				$('#PS_INFINITE_AJAX_LOADER').keyup(function(event){
				    delay(function(){
					myloader = $('#PS_INFINITE_AJAX_LOADER').val();
					$('input[type=radio][name=loadimg]').each(function () {
						if ($(this).val() == myloader)
							$(this).attr('checked', 'checked');
						else
							$(this).removeAttr('checked');
					});
				    }, 500);
				});
			});
			//]]>
			</script>";

        }
}
?>