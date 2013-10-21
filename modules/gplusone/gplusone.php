<?php

class gPlusOne extends Module
{
 	private $_html = '';
	private $_gpo_layout = '';
	private $_gpo_count = '';
	private $_full_version = 13000;
 	private $_last_updated = '';
	
	
 	function __construct()
	{
		$this->name = 'gplusone';
		$this->tab = floatval(substr(_PS_VERSION_,0,3))<1.4?'Presto-Changeo':'social_networks';
		$this->version = '1.3';
		if (floatval(substr(_PS_VERSION_,0,3)) >= 1.4)
			$this->author = 'Presto-Changeo';
		
		parent::__construct();
		$this->_refreshProperties();
		
		$this->displayName = $this->l('Google Plus One');
		$this->description = $this->l('Adds a Google "Plus One" button to the product page');
		if ($this->upgradeCheck('GPO'))
			$this->warning = $this->l('We have released a new version of the module,') .' '.$this->l('request an upgrade at ').' https://www.presto-changeo.com/en/contact_us';

	}
	
	
	function install()
	{
		if (!parent::install())
			return false;
		$hooked = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'hook` WHERE name = "googlePlusOne"');
		if (!is_array($hooked) || sizeof($hooked) == 0)
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'hook` (
			`id_hook` ,`name` ,`title` ,`description` ,`position`)
			VALUES (NULL , "googlePlusOne", "Google Plus One", "Custom hook for Google Plus One Module", "1");');
		if (!$this->registerHook('extraLeft') || !$this->registerHook('googlePlusOne') || !$this->registerHook('header'))
			return false;
		Configuration::updateValue('GPO_LAYOUT','standard');			
		Configuration::updateValue('GPO_COUNT','1');			
		return true;
	}

	private function _refreshProperties()
	{
		$this->_gpo_layout = Configuration::get('GPO_LAYOUT');
		$this->_gpo_count = Configuration::get('GPO_COUNT');
	}

	public function getContent()
	{
		$ps_version  = floatval(substr(_PS_VERSION_,0,3));
		$this->_html = ''.($ps_version >= 1.5 ? '<div style="width: 850px; margin: 0 auto;"> ' : '').' <img src="http://updates.presto-changeo.com/logo.jpg" border="0" /> <h2>'.$this->displayName.'</h2>';
		$this->_postProcess();
		$this->_displayForm();
		return $this->_html.''.($ps_version >= 1.5 ? '</div> ' : '');
	}
	
    private function _displayForm()
    {
    	global $cookie;
		$ps_version  = floatval(substr(_PS_VERSION_,0,3));
		
		if ($url = $this->upgradeCheck('GPO'))
			$this->_html .= '
			<fieldset class="width3" style="background-color:#FFFAC6;width:800px;"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('New Version Available').'</legend>
			'.$this->l('We have released a new version of the module. For a list of new features, improvements and bug fixes, view the ').'<a href="'.$url.'" target="_index"><b><u>'.$this->l('Change Log').'</b></u></a> '.$this->l('on our site.').'
			<br />
			'.$this->l('For real-time alerts about module updates, be sure to join us on our') .' <a href="http://www.facebook.com/pages/Presto-Changeo/333091712684" target="_index"><u><b>Facebook</b></u></a> / <a href="http://twitter.com/prestochangeo1" target="_index"><u><b>Twitter</b></u></a> '.$this->l('pages').'.
			<br />
			<br />
			'.$this->l('Please').' <a href="https://www.presto-changeo.com/en/contact_us" target="_index"><b><u>'.$this->l('contact us').'</u></b></a> '.$this->l('to request an upgrade to the latest version').'.
			</fieldset><br />';

			
    	$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" name="gplusone_form" id="gplusone_form" method="post">
			<fieldset class="width3" style="width:850px"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Installation Instructions (Optional)').'</legend>
				<b style="color:blue">'.$this->l('To display the "Plus One" button in a different hook').'</b>:
				<br />
				<br />
				'.$this->l('Add').' <b style="color:green">'.( $ps_version <= 1.4 ? $this->l('{$HOOK_GOOGLE_PLUS_ONE}'): '{hook h="googlePlusOne"}' ).'</b> '.$this->l('in the tpl file you want it to show').'.
				<br />
				<br />
				'.( $ps_version < 1.5  ? ($ps_version >= 1.4 ? $this->l('Copy /modules/gplusone/override_1.4/classes/FrontController.php to /override/classes/ (If the file already exists, you will have to merge both files)'):$this->l('Add').' <b style="color:green">\'HOOK_GOOGLE_PLUS_ONE\' => Module::hookExec(\'googlePlusOne\'),</b> '.$this->l('to /header.php below HOOK_TOP around line #15.')) : '' ).'
			</fieldset>
			<br />
		<fieldset class="width3" style="width:850px"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Google Plus One Settings').'</legend>
			<table border="0" width="850">
			<tr height="30">
				<td align="left" valign="top" width="120">
					<b>'.$this->l('Layout Style').':</b> 
				</td>
				<td align="left" valign="top">
   					<select name="gpo_layout" style="width:150px">
   						<option value="small" '.(Tools::getValue('gpo_layout', $this->_gpo_layout) == "small"?"selected":"").'>'.$this->l('Small').'</option>
   						<option value="medium" '.(Tools::getValue('gpo_layout', $this->_gpo_layout) == "medium"?"selected":"").'>'.$this->l('Medium').'</option>
   						<option value="standard" '.(Tools::getValue('gpo_layout', $this->_gpo_layout) == "standard"?"selected":"").'>'.$this->l('Standard').'</option>
   						<option value="tall" '.(Tools::getValue('gpo_layout', $this->_gpo_layout) == "tall"?"selected":"").'>'.$this->l('Tall').'</option>
   					</select>
				</td>
			</tr>
			<tr height="30">
				<td align="left" valign="top">
					<b>'.$this->l('Show Count').':</b> 
				</td>
				<td align="left" valign="top">
   					<select name="gpo_count" style="width:150px">
   						<option value="0" '.(Tools::getValue('gpo_count', $this->_gpo_count) == "0"?"selected":"").'>'.$this->l('No').'</option>
   						<option value="1" '.(Tools::getValue('gpo_count', $this->_gpo_count) == "1"?"selected":"").'>'.$this->l('Yes').'</option>
   					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" value="'.$this->l('Update').'" name="submitChanges" class="button" />
				</td>
			</tr>
			</table>
			</fieldset>
		</form>';
   	}
    	    
	private function _postProcess()
	{
		if (Tools::isSubmit('submitChanges'))
		{
			if (!Configuration::updateValue('GPO_LAYOUT', Tools::getValue('gpo_layout'))
				|| !Configuration::updateValue('GPO_COUNT', Tools::getValue('gpo_count')))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			else
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		$this->_refreshProperties();
	}
	
	function hookExtraLeft($params)
	{
		global $smarty;
		$smarty->assign(array('gpo_layout' => $this->_gpo_layout, 'gpo_count' => $this->_gpo_count,
		'gpo_default_hook' => isset($params['gpo_hookGooglePlusOne'])?0:1));
		return $this->display(__FILE__, 'gplusone.tpl');
	}

	function hookHeader()
	{
		global $smarty, $cookie, $link;
		$lang = strtolower(Language::getIsoById($cookie->id_lang));
		if ($lang == 'pt')
			$lang = 'pt_PT';
		elseif ($lang == 'zh')
			$lang = 'zh_CN';
		elseif ($lang == 'tw')
			$lang = 'zh_TW';
		elseif ($lang == 'he')
			$lang = 'iw';
		elseif ($lang == 'ar')
			$lang = 'ar';
		elseif ($lang == 'bg')
			$lang = 'bg';
		elseif ($lang == 'ca')
			$lang = 'ca';
		elseif ($lang == 'hr')
			$lang = 'hr';
		elseif ($lang == 'cs')
			$lang = 'cs';
		elseif ($lang == 'da')
			$lang = 'da';
		elseif ($lang == 'fl')
			$lang = 'fl';
		elseif ($lang == 'fr')
			$lang = 'fr';
		elseif ($lang == 'de')
			$lang = 'de';
		elseif ($lang == 'el')
			$lang = 'el';
		elseif ($lang == 'hi')
			$lang = 'hi';
		elseif ($lang == 'hu')
			$lang = 'hu';
		elseif ($lang == 'id')
			$lang = 'id';
		elseif ($lang == 'it')
			$lang = 'it';
		elseif ($lang == 'ja')
			$lang = 'ja';
		elseif ($lang == 'ko')
			$lang = 'ko';
		elseif ($lang == 'lv')
			$lang = 'lv';
		elseif ($lang == 'lt')
			$lang = 'lt';
		elseif ($lang == 'ms')
			$lang = 'ms';
		elseif ($lang == 'no')
			$lang = 'no';
		elseif ($lang == 'fa')
			$lang = 'fa';
		elseif ($lang == 'pl')
			$lang = 'pl';
		elseif ($lang == 'ro')
			$lang = 'ro';
		elseif ($lang == 'ru')
			$lang = 'ru';
		elseif ($lang == 'sr')
			$lang = 'sr';
		elseif ($lang == 'sk')
			$lang = 'sk';
		elseif ($lang == 'sl')
			$lang = 'sl';
		elseif ($lang == 'es')
			$lang = 'es';
		elseif ($lang == 'sv')
			$lang = 'sv';
		elseif ($lang == 'th')
			$lang = 'th';
		elseif ($lang == 'tr')
			$lang = 'tr';
		elseif ($lang == 'uk')
			$lang = 'uk';
		elseif ($lang == 'vi')
			$lang = 'vi';
		else
			$lang = 'en_US';
			
		
    	$ps_version3  = substr(_PS_VERSION_,0,5);
    	$psv = floatval(substr(_PS_VERSION_,0,3));
		$protocol_link = @$_SERVER['HTTPS'] == "on"?"https://":"http://";
		
		$cover = Product::getCover(intval(Tools::getValue('id_product')));
		$gpo_cover = '';
		if (is_array($cover) && sizeof($cover) == 1)
		{
			$product = new Product((int)Tools::getValue('id_product'));
			if ($psv >= 1.4)
				$gpo_cover = $link->getImageLink($product->link_rewrite[$cookie->id_lang], Tools::getValue('id_product').'-'.$cover['id_image'],'thickbox'.($psv >= 1.5 && ($ps_version3 != '1.5.0' && $ps_version3 != '1.5.1')?'_default':''));
			else
				$gpo_cover = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'img/p/'.Tools::getValue('id_product').'-'.$cover['id_image'].'.jpg';
		}
		$smarty->assign('gpo_cover', $gpo_cover);
		$smarty->assign('gpo_lang_code', $lang);
		return $this->display(__FILE__, 'header.tpl');
	}

	function hookHome($params)
	{
		return $this->hookExtraLeft($params);
	}

	function hookGooglePlusOne($params)
	{
		$params['gpo_hookGooglePlusOne'] = 1;
		return $this->hookExtraLeft($params);
	}
	
	private function upgradeCheck($module)
	{
		global $cookie;
		$ps_version  = floatval(substr(_PS_VERSION_,0,3));
		// Only run upgrae check if module is loaded in the backoffice.
		if (($ps_version > 1.1  && $ps_version < 1.5) && (!is_object($cookie) || !$cookie->isLoggedBack()))
			return;
		if ($ps_version >= 1.5)
		{
			$context = Context::getContext();
			if (!isset($context->employee) || !$context->employee->isLoggedBack())
				return;			
		}
		// Get Presto-Changeo's module version info
		$mod_info_str = Configuration::get('PRESTO_CHANGEO_SV');
		if (!function_exists('json_decode'))
		{
			if (!file_exists(dirname(__FILE__).'/JSON.php'))
				return false; 
			include_once(dirname(__FILE__).'/JSON.php');
			$j = new JSON();
			$mod_info = $j->unserialize($mod_info_str);
		}
		else
			$mod_info = json_decode($mod_info_str);
		// Get last update time.
		$time = time();
		// If not set, assign it the current time, and skip the check for the next 7 days. 
		if ($this->_last_updated <= 0)
		{
			Configuration::updateValue('PRESTO_CHANGEO_UC', $time);
			$this->_last_updated = $time;
		}
		// If haven't checked in the last 1-7+ days
		$update_frequency = max(86400, isset($mod_info->{$module}->{'T'})?$mod_info->{$module}->{'T'}:86400);
		if ($this->_last_updated < $time - $update_frequency)
		{	
			// If server version number exists and is different that current version, return URL
			if (isset($mod_info->{$module}->{'V'}) && $mod_info->{$module}->{'V'} > $this->_full_version)
				return $mod_info->{$module}->{'U'};
			$url = 'http://updates.presto-changeo.com/?module_info='.$module.'_'.$this->version.'_'.$this->_last_updated.'_'.$time.'_'.$update_frequency;
			$mod = @file_get_contents($url);
			if ($mod == '' && function_exists('curl_init'))
			{
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
				$mod = curl_exec($ch);
			}
			Configuration::updateValue('PRESTO_CHANGEO_UC', $time);
			$this->_last_updated = $time;
			if (!function_exists('json_decode') )
			{
				$j = new JSON();
				$mod_info = $j->unserialize($mod);
			}
			else
				$mod_info = json_decode($mod);
			if (!isset($mod_info->{$module}->{'V'}))
				return false;
			if (Validate::isCleanHtml($mod))
				Configuration::updateValue('PRESTO_CHANGEO_SV', $mod);
			if ($mod_info->{$module}->{'V'} > $this->_full_version)
				return $mod_info->{$module}->{'U'};
			else 
				return false;
		}
		elseif (isset($mod_info->{$module}->{'V'}) && $mod_info->{$module}->{'V'} > $this->_full_version)
			return $mod_info->{$module}->{'U'};
		else
			return false;
	}	
}
?>