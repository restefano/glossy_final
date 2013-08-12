<?php

if (floatval(substr(_PS_VERSION_,0,3)) >= 1.3 && !defined('_CAN_LOAD_FILES_'))
exit;

require_once(_PS_MODULE_DIR_ . 'twitterbutton/PrestoChangeoClasses/init.php');

class TwitterButton extends PrestoChangeoModule
{
	protected $_html = '';
	protected $_full_version = 10000;
	protected $_last_updated = '';

	private $_tw_button = '';
	private $_tw_button_size = '';
	private $_tw_text = '';
	private $_tw_count = '';
	private $_tw_by = '';
	private $_tw_tag = '';
	private $_tw_lang = '';

	function __construct()
	{
		$this->name = 'twitterbutton';
		$this->tab = $this->getPSV()<1.4?'Presto-Changeo':'social_networks';
		$this->version = '1.0';
		if ($this->getPSV() >= 1.4)
		$this->author = 'Presto-Changeo';

		parent::__construct();
		$this->_refreshProperties();

		$this->displayName = $this->l('Twitter Button');
		$this->description = $this->l('Adds a Twitter button to product page');
		if ($this->upgradeCheck('TWB'))
		$this->warning = $this->l('We have released a new version of the module,') .' '.$this->l('download by adding to your cart and proceeding through checkout from: ').' http://www.presto-changeo.com/en/7-prestashop-free-modules';
		 
	}


	function install()
	{
		if (!parent::install())
			return false;
		$hooked = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'hook` WHERE name = "twitterButton"');
		if (!is_array($hooked) || sizeof($hooked) == 0)
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'hook` (
			`id_hook` ,`name` ,`title` ,`description` ,`position`)
			VALUES (NULL , "twitterButton", "Twitter Share", "Custom hook for Twitter Button Module", "1");');
		if (!$this->registerHook('extraLeft') || !$this->registerHook('twitterButton') || !$this->registerHook('header'))
			return false;

		$this->_installConfiguration();
		return true;
	}

	function _installConfiguration($confName = NULL)
	{
		if($this->getPSV() >= 1.5){
			$id_shop = (int)$this->context->shop->id;
			$shopLangs = Language::getLanguages(true, $id_shop);
		}else
		$shopLangs = Language::getLanguages();

		$tw_lang = '';
		foreach($shopLangs as $shopLang){
			$isoCode = $this->getLangMatchForTwitter($shopLang['iso_code']);

			$tw_lang .= '-'.$shopLang['iso_code'].'='.$isoCode['twitterCode'].'-';
		}

		$configs['TW_BUTTON'] = 'share';
		$configs['TW_BUTTON_SIZE'] = 'small';
		$configs['TW_TEXT'] = 'Look what I just found...';
		$configs['TW_COUNT'] = '1';
		$configs['TW_BY'] = 'Your Username';
		$configs['TW_TAG'] = 'Tag';
		$configs['TW_LANG'] = $tw_lang;
		$configs['PRESTO_CHANGEO_UC'] = time();

		if(is_null($confName)){
			foreach($configs as $name => $value){
				Configuration::updateValue($name, $value);
			}
		}else{
			Configuration::updateValue($confName, $configs[$confName]);
		}

		return true;
	}


	private function _refreshProperties()
	{
		$this->verifConfiguration();

		$this->_tw_button = Configuration::get('TW_BUTTON');
		$this->_tw_button_size = Configuration::get('TW_BUTTON_SIZE');
		$this->_tw_text = Configuration::get('TW_TEXT');
		$this->_tw_count = Configuration::get('TW_COUNT');
		$this->_tw_by = Configuration::get('TW_BY');
		$this->_tw_tag = Configuration::get('TW_TAG');
		$this->_tw_lang = Configuration::get('TW_LANG');
		$this->_last_updated = Configuration::get('PRESTO_CHANGEO_UC');
	}

	public function getContent()
	{
		$this->_html = ''.($this->getPSV() >= 1.5 ? '<div style="width: 850px; margin: 0 auto;"> ' : '').' <img src="http://updates.presto-changeo.com/logo.jpg" border="0" /> <h2>'.$this->displayName.'</h2>';
		$this->_postProcess();
		$this->_displayForm();
		return $this->_html.''.($this->getPSV() >= 1.5 ? '</div> ' : '');
	}

	private function _displayForm()
	{

		if($this->getPSV() >= 1.5)
		$id_shop = (int)$this->context->shop->id;


		if ($url = $this->upgradeCheck('TWB'))
		$this->_html .= '

                    <fieldset class="width3" style="background-color:#FFFAC6;width:800px;"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('New Version Available').'</legend>
                    '.$this->l('We have released a new version of the module. For a list of new features, improvements and bug fixes, view the ').'<a href="'.$url.'" target="_index"><b><u>'.$this->l('Change Log').'</b></u></a> '.$this->l('on our site.').'
                    <br />
                    '.$this->l('For real-time alerts about module updates, be sure to join us on our') .' <a href="http://www.facebook.com/pages/Presto-Changeo/333091712684" target="_index"><u><b>Facebook</b></u></a> / <a href="http://twitter.com/prestochangeo1" target="_index"><u><b>Twitter</b></u></a> '.$this->l('pages').'.
                    <br />
                    <br />
                    '.$this->l('Please').' <a href="https://www.presto-changeo.com/en/contact_us" target="_index"><b><u>'.$this->l('contact us').'</u></b></a> '.$this->l('to request an upgrade to the latest version (Free modules should be downloaded directly from our site again)').'.
                    </fieldset><br />';
		 
		$this->_html .= '
                    <form action="'.$_SERVER['REQUEST_URI'].'" name="twitterbutton_form" id="twitterbutton_form" method="post">
                            <fieldset class="width3" style="width:850px"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Installation Instructions (Optional)').'</legend>
                                    <b style="color:blue">'.$this->l('To display the button in a different hook').'</b>:
                                    <br />
                                    <br />
                                    '.$this->l('Add').' <b style="color:green">'.($this->getPSV() <= 1.4 ? $this->l('{$HOOK_TWITTER_BUTTON}') : '{hook h="twitterButton"}').'</b> '.$this->l('in the tpl file you want it to show').'.
                                    <br />
                                    <br />';

		if ($this->getPSV() == 1.4)
			$this->_html .= $this->l('Copy /modules/twitterbutton/override/classes/FrontController.php to /override/classes/ (If the file already exists, you will have to merge both files)');
		else if ($this->getPSV() < 1.4)
			$this->_html .=$this->l('Add').' <b style="color:green">\'HOOK_TWITTER_BUTTON\' => Module::hookExec(\'twitterButton\'),</b> '.$this->l('to /header.php below HOOK_TOP around line #15');

		$this->_html .= '</fieldset>
                            <br />
                    <fieldset class="width3" style="width:850px"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Twitter Button Settings').'</legend>
                            <table border="0" width="850">
                            <tr height="30">
                                    <td align="left" valign="top">
                                            <b>'.$this->l('Twitter Username').':</b> 
                                    </td>
                                    <td align="left" valign="top">
                                            @<input type="text" style="width:140px" name="tw_by" value="'.Tools::getValue('tw_by', $this->_tw_by).'">
                                    </td>
                            </tr>
                            <tr height="30">
                                    <td align="left" valign="top">
                                            <b>'.$this->l('Twitter Tag').':</b> 
                                    </td>
                                    <td align="left" valign="top">
                                            #<input type="text" style="width:140px" name="tw_tag" value="'.Tools::getValue('tw_tag', $this->_tw_tag).'">
                                    </td>
                            </tr>
                            <tr height="30">
                                    <td align="left" valign="top" width="120">
                                            <b>'.$this->l('Button Type').':</b> 
                                    </td>
                                    <td align="left" valign="top">
                                            <select name="tw_button" style="width:150px">
                                                    <option value="share" '.(Tools::getValue('tw_button', $this->_tw_button) == "share"?"selected":"").'>'.$this->l('Share Button').'</option>
                                                    <option value="follow" '.(Tools::getValue('tw_button', $this->_tw_button) == "follow"?"selected":"").'>'.$this->l('Follow Me Button').'</option>
                                                    <option value="tag" '.(Tools::getValue('tw_button', $this->_tw_button) == "tag"?"selected":"").'>'.$this->l('Mention Tag Button').'</option>
                                                    <option value="mention" '.(Tools::getValue('tw_button', $this->_tw_button) == "mention"?"selected":"").'>'.$this->l('Send to User Button').'</option>
                                            </select>
                                    </td>
                            </tr>
                            <tr height="30">
                                    <td align="left" valign="top">
                                            <b>'.$this->l('Button Size').':</b> 
                                    </td>
                                    <td align="left" valign="top">
                                            <select name="tw_button_size" style="width:150px">
                                                    <option value="small" '.(Tools::getValue('tw_button_size', $this->_tw_button_size) == "small"?"selected":"").'>'.$this->l('Small').'</option>
                                                    <option value="large" '.(Tools::getValue('tw_button_size', $this->_tw_button_size) == "large"?"selected":"").'>'.$this->l('Large').'</option>
                                            </select>
                                    </td>
                            </tr>
                            <tr height="30">
                                    <td align="left" valign="top">
                                            <b>'.$this->l('Tweet Text').':</b> 
                                    </td>
                                    <td align="left" valign="top">
                                            <input type="text" style="width:140px" name="tw_text" value="'.Tools::getValue('tw_text', $this->_tw_text).'">
                                    </td>
                            </tr>
                            <tr height="30">
                                    <td align="left" valign="top">
                                            <b>'.$this->l('Counter').':</b> 
                                    </td>
                                    <td align="left" valign="top">
                                            <select name="tw_count" style="width:150px">
                                                    <option value="1" '.(Tools::getValue('tw_count', $this->_tw_count) == "1"?"selected":"").'>'.$this->l('Yes').'</option>
                                                    <option value="0" '.(Tools::getValue('tw_count', $this->_tw_count) == "0"?"selected":"").'>'.$this->l('No').'</option>
                                            </select>
                                            '.$this->l('Share and Follow button only').'
                                    </td>
                            </tr>
                            <tr height="30">
                                    <td align="left" valign="top" colspan="3">
                                            <b>'.$this->l('Button Language').'</b> 
                                    </td>
                            </tr>';

		if($this->getPSV() >= 1.5){
			$id_shop = (int)$this->context->shop->id;
			$shopLangs = Language::getLanguages(true, $id_shop);
		}else
		$shopLangs = Language::getLanguages();

		$langsButtonE = explode('-', $this->_tw_lang);

		$langsButton = array();
		foreach($langsButtonE as $langE){
			if(strlen($langE)){
				$langE = explode('=', $langE);
				$langsButton[$langE[0]] = $langE[1];
			}
		}

		foreach($shopLangs as $shopLang){

			if(!strlen($langsButton[$shopLang['iso_code']])){
				$this->_installConfiguration('TW_LANG');
				$this->_refreshProperties();

				$langsButtonE = explode('-', $this->_tw_lang);

				$langsButton = array();
				foreach($langsButtonE as $langE){
					if(strlen($langE)){
						$langE = explode('=', $langE);
						$langsButton[$langE[0]] = $langE[1];
					}
				}
			}

			$this->_html .= '
                                    <tr height="30">
                                        <td align="left" valign="top">'.$shopLang['name'].'</td>
                                        <td align="left" valign="top" colspan="2">
                                            <select name="tw_lang['.$shopLang['iso_code'].']">
                                                <option value="fr" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "fr"?"selected":"").' >French</option>
                                                <option value="en" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "en" 
                                                || Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "gb" ?"selected":"").' >English</option>
                                                <option value="ar" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ar"?"selected":"").' >Arabic</option>
                                                <option value="ja" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ja"?"selected":"").' >Japanese</option>
                                                <option value="es" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "es" 
                                                || Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ag"
                                                || Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "cb"
                                                || Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "mx" ? "selected":"").' >Spanish</option>
                                                <option value="de" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "de"?"selected":"").' >German</option>
                                                <option value="it" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "it"?"selected":"").' >Italian</option>
                                                <option value="id" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "id"?"selected":"").' >Indonesian</option>
                                                <option value="pt" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "pt"
                                                || Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "br" ?"selected":"").' >Portuguese</option>
                                                <option value="ko" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ko"?"selected":"").' >Korean</option>
                                                <option value="tr" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "tr"?"selected":"").' >Turkish</option>
                                                <option value="ru" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ru"?"selected":"").' >Russian</option>
                                                <option value="nl" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "nl"?"selected":"").' >Dutch</option>
                                                <option value="fil" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "fil"?"selected":"").' >Filipino</option>
                                                <option value="msa" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ms"?"selected":"").' >Malay</option>
                                                <option value="zh-tw" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "tw"?"selected":"").' >Traditional Chinese</option>
                                                <option value="zh-cn" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "zh"?"selected":"").' >Simplified Chinese</option>
                                                <option value="hi" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "hi"?"selected":"").' >Hindi</option>
                                                <option value="no" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "no"?"selected":"").' >Norwegian</option>
                                                <option value="sv" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "sv"?"selected":"").' >Swedish</option>
                                                <option value="fi" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "fi"?"selected":"").' >Finnish</option>
                                                <option value="da" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "da"?"selected":"").' >Danish</option>
                                                <option value="pl" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "pl"?"selected":"").' >Polish</option>
                                                <option value="hu" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "hu"?"selected":"").' >Hungarian</option>
                                                <option value="fa" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "fa"?"selected":"").' >Farsi</option>
                                                <option value="he" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "he"?"selected":"").' >Hebrew</option>
                                                <option value="ur" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ur"?"selected":"").' >Urdu</option>
                                                <option value="th" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "th"?"selected":"").' >Thai</option>
                                                <option value="uk" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "uk"?"selected":"").' >Ukrainian</option>
                                                <option value="ca" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ca"?"selected":"").' >Catalan</option>
                                                <option value="el" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "el"?"selected":"").' >Greek</option>
                                                <option value="eu" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "eu"?"selected":"").' >Basque</option>
                                                <option value="cs" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "cs"?"selected":"").' >Czech</option>
                                                <option value="xx-lc" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "xx-lc"?"selected":"").' >Lolcat</option>
                                                <option value="gl" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "gl"?"selected":"").' >Galician</option>
                                                <option value="ro" '.(Tools::getValue('tw_lang['.$shopLang['iso_code'].']', $langsButton[$shopLang['iso_code']]) == "ro"?"selected":"").' >Romanian</option>
                                            </select>
                                        </td>
                                    </tr>
                                ';
		}

		$this->_html .= '
                            <tr height="30">
                                    <td align="left" valign="top" colspan="2">

                                    </td>
                            </tr>
                            <tr>
                                    <td colspan="2" align="center">
                                            <input type="submit" value="'.$this->l('Update').'" name="submitChanges" class="button" />
                                    </td>
                            </tr>
                            </table>
                            </fieldset>
                    </form>

                    ';
	}
		
	private function _postProcess()
	{
		if (Tools::isSubmit('submitChanges'))
		{
			$tw_lang = '';
			foreach(Tools::getValue('tw_lang') as $isoCode => $value){
				$tw_lang .= '-'.$isoCode.'='.$value.'-';
			}
			if (!Configuration::updateValue('TW_BUTTON', Tools::getValue('tw_button'))
			|| !Configuration::updateValue('TW_BUTTON_SIZE', Tools::getValue('tw_button_size'))
			|| !Configuration::updateValue('TW_TEXT', Tools::getValue('tw_text'))
			|| !Configuration::updateValue('TW_COUNT', Tools::getValue('tw_count'))
			|| !Configuration::updateValue('TW_BY', Tools::getValue('tw_by'))
			|| !Configuration::updateValue('TW_TAG', Tools::getValue('tw_tag'))
			|| !Configuration::updateValue('TW_LANG', $tw_lang))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			else
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		$this->_refreshProperties();
	}

	function hookExtraLeft($params)
	{
		$smarty = $this->context->smarty;
		$cookie = $this->context->cookie;

		if (stripos($_SERVER['HTTP_USER_AGENT'],'bot') !== false ||
			stripos($_SERVER['HTTP_USER_AGENT'],'baidu') !== false ||
			stripos($_SERVER['HTTP_USER_AGENT'],'spider') !== false ||
			stripos($_SERVER['HTTP_USER_AGENT'],'Ask Jeeves') !== false ||
			stripos($_SERVER['HTTP_USER_AGENT'],'slurp') !== false ||
			stripos($_SERVER['HTTP_USER_AGENT'],'crawl') !== false)
		return;

		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		$langsButtonE = explode('-', $this->_tw_lang);

		$langsButton = array();
		foreach($langsButtonE as $langE){
			if(strlen($langE)){
				$langE = explode('=', $langE);
				$langsButton[$langE[0]] = $langE[1];
			}
		}

		$langCode = $langsButton[strtolower(Language::getIsoById($cookie->id_lang))];

		$smarty->assign(array('tw_button' => $this->_tw_button, 'tw_button_size' => $this->_tw_button_size,'tw_text' => $this->_tw_text,
			'tw_count' => $this->_tw_count,'tw_by' => $this->_tw_by,'tw_tag' => $this->_tw_tag,'tw_lang' => $langCode,
			'tw_default_hook' => isset($params['tw_hookTwitterButton'])?0:1));
		return $this->display(__FILE__, 'views/templates/front/twitterbutton.tpl');
	}

	function hookHome($params)
	{
		return $this->hookExtraLeft($params);
	}

	function hookTwitterButton($params)
	{
		$params['tw_hookTwitterButton'] = 1;
		return $this->hookExtraLeft($params);
	}
	/*
	 private function upgradeCheck($module)
	 {
		$cookie = $this->context->cookie;
		// Only run upgrae check if module is loaded in the backoffice.
		if (($this->getPSV() > 1.1  && $this->getPSV() < 1.5) && (!is_object($cookie) || !$cookie->isLoggedBack()))
		return;
		if ($this->getPSV() >= 1.5)
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
		*/

	function verifConfiguration()
	{
		if(!strlen(Configuration::get('TW_BUTTON')))
			$this->_installConfiguration('TW_BUTTON');

		if(!strlen(Configuration::get('TW_BUTTON_SIZE')))
			$this->_installConfiguration('TW_BUTTON_SIZE');

		if(!strlen(Configuration::get('TW_COUNT')))
			$this->_installConfiguration('TW_COUNT');

		if(!strlen(Configuration::get('TW_BY')))
			$this->_installConfiguration('TW_BY');

		if(!strlen(Configuration::get('TW_TAG')))
			$this->_installConfiguration('TW_TAG');

		if(!strlen(Configuration::get('TW_LANG')))
			$this->_installConfiguration('TW_LANG');

		if(!strlen(Configuration::get('PRESTO_CHANGEO_UC')))
			$this->_installConfiguration('PRESTO_CHANGEO_UC');

		return true;
	}

	function getLangMatchForTwitter($isoCode, $key = 0)
	{
		$isoCodes = array(
                'af' => array(
                    'name'          => 'Afrikaans',
                    'twitterCode'   => ''
                    ),
                'sq' => array(
                    'name'          => 'Albanian',
                    'twitterCode'   => ''
                    ),
                'ar' => array(
                    'name'          => 'Arabic',
                    'twitterCode'   => 'ar'
                    ),
                'az' => array(
                    'name'          => 'Azerbaijani',
                    'twitterCode'   => ''
                    ),
                'bz' => array(
                    'name'          => 'Breton',
                    'twitterCode'   => ''
                    ),
                'bg' => array(
                    'name'          => 'Bulgarian',
                    'twitterCode'   => ''
                    ),
                'ca' => array(
                    'name'          => 'Catalan',
                    'twitterCode'   => 'ca'
                    ),
                'zh' => array(
                    'name'          => 'Chinese-Simplified',
                    'twitterCode'   => 'zh-cn'
                    ),
                'tw' => array(
                    'name'          => 'Chinese-Traditional',
                    'twitterCode'   => 'zh-tw'
                    ),
                'hr' => array(
                    'name'          => 'Croatian',
                    'twitterCode'   => ''
                    ),
                'cs' => array(
                    'name'          => 'Czech',
                    'twitterCode'   => 'cs'
                    ),
                'da' => array(
                    'name'          => 'Danish',
                    'twitterCode'   => 'da'
                    ),
                'nl' => array(
                    'name'          => 'Dutch',
                    'twitterCode'   => 'nl'
                    ),
                'en' => array(
                    'name'          => 'English',
                    'twitterCode'   => 'en'
                    ),
                'gb' => array(
                    'name'          => 'English (United Kingdom)',
                    'twitterCode'   => 'en'
                    ),
                'et' => array(
                    'name'          => 'Estonian',
                    'twitterCode'   => ''
                    ),
                'fi' => array(
                    'name'          => 'Finnish',
                    'twitterCode'   => 'fi'
                    ),
                'fr' => array(
                    'name'          => 'French',
                    'twitterCode'   => 'fr'
                    ),
                'gl' => array(
                    'name'          => 'Galician',
                    'twitterCode'   => 'gl'
                    ),
                'ka' => array(
                    'name'          => 'Georgian',
                    'twitterCode'   => ''
                    ),
                'de' => array(
                    'name'          => 'German',
                    'twitterCode'   => 'de'
                    ),
                'el' => array(
                    'name'          => 'Greek',
                    'twitterCode'   => 'el'
                    ),
                'he' => array(
                    'name'          => 'Hebrew',
                    'twitterCode'   => 'he'
                    ),
                'hu' => array(
                    'name'          => 'Hungarian',
                    'twitterCode'   => 'hu'
                    ),
                'id' => array(
                    'name'          => 'Indonesian',
                    'twitterCode'   => 'id'
                    ),
                'ga' => array(
                    'name'          => 'Irish',
                    'twitterCode'   => ''
                    ),
                'it' => array(
                    'name'          => 'Italian',
                    'twitterCode'   => 'it'
                    ),
                'ja' => array(
                    'name'          => 'Japanese',
                    'twitterCode'   => 'ja'
                    ),
                'ko' => array(
                    'name'          => 'Korean',
                    'twitterCode'   => 'ko'
                    ),
                'lo' => array(
                    'name'          => 'Lao',
                    'twitterCode'   => ''
                    ),
                'lv' => array(
                    'name'          => 'Latvian',
                    'twitterCode'   => ''
                    ),
                'lt' => array(
                    'name'          => 'Lithuanian',
                    'twitterCode'   => ''
                    ),
                'mk' => array(
                    'name'          => 'Macedonian',
                    'twitterCode'   => ''
                    ),
                'ms' => array(
                    'name'          => 'Malay',
                    'twitterCode'   => 'msa'
                    ),
                'ml' => array(
                    'name'          => 'Malayalam',
                    'twitterCode'   => ''
                    ),
                'no' => array(
                    'name'          => 'Norwegian',
                    'twitterCode'   => 'no'
                    ),
                'fa' => array(
                    'name'          => 'Persian',
                    'twitterCode'   => ''
                    ),
                'pl' => array(
                    'name'          => 'Polish',
                    'twitterCode'   => 'pl'
                    ),
                'br' => array(
                    'name'          => 'Português (Brasil)',
                    'twitterCode'   => 'pt'
                    ),
                'pt' => array(
                    'name'          => 'Português (Portuguese)',
                    'twitterCode'   => 'pt'
                    ),
                'ro' => array(
                    'name'          => 'Romanian',
                    'twitterCode'   => 'ro'
                    ),
                'ru' => array(
                    'name'          => 'Russian',
                    'twitterCode'   => 'ru'
                    ),
                'sr' => array(
                    'name'          => 'Serbian',
                    'twitterCode'   => ''
                    ),
                'sk' => array(
                    'name'          => 'Slovak',
                    'twitterCode'   => ''
                    ),
                'si' => array(
                    'name'          => 'Slovene',
                    'twitterCode'   => ''
                    ),
                'es' => array(
                    'name'          => 'Spanish',
                    'twitterCode'   => 'es'
                    ),
                'ag' => array(
                    'name'          => 'Spanish-Argentine',
                    'twitterCode'   => 'es'
                    ),
                'cb' => array(
                    'name'          => 'Spanish-Colombia',
                    'twitterCode'   => 'es'
                    ),
                'mx' => array(
                    'name'          => 'Spanish-Mexican',
                    'twitterCode'   => 'es'
                    ),
                'sv' => array(
                    'name'          => 'Swedish',
                    'twitterCode'   => 'sv'
                    ),
                'th' => array(
                    'name'          => 'Thai',
                    'twitterCode'   => 'th'
                    ),
                'vn' => array(
                    'name'          => 'Ti?ng Vi?t (Vietnamese)',
                    'twitterCode'   => ''
                    ),
                'tr' => array(
                    'name'          => 'Turkish',
                    'twitterCode'   => 'tr'
                    ),
                'uk' => array(
                    'name'          => 'Ukrainian',
                    'twitterCode'   => 'uk'
                    ),
                'ur' => array(
                    'name'          => 'Urdu',
                    'twitterCode'   => 'ur'
                    ),
                'ug' => array(
                    'name'          => 'Uyghur',
                    'twitterCode'   => ''
                    )
                    );

                    $lang = $isoCodes[$isoCode];

                    if(!strlen($lang['twitterCode']))
                    $lang = array(
                    'name'          => "English (Shop language is not avaliable on Twitter)",
                    'twitterCode'   => "en"
                    );

                    return $lang;
	}
}
?>