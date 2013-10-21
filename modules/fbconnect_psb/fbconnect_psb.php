<?php
/*
* 2013 Ha!*!*y
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* It is available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*
* DISCLAIMER
* This code is provided as is without any warranty.
* No promise of safety or security.
*
*  @author          Ha!*!*y <ha99ys@gmail.com>
*  @copyright       2013 Ha!*!*y
*  @license         http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
  exit;

class FBConnect_PSB extends Module
{
	public function __construct()
	{
		$this->name = 'fbconnect_psb';
		$this->tab = 'social_networks';
		$this->author = 'Ha!*!*y';
		$this->version = '1.0b';

		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

		parent::__construct();

		$this->displayName = $this->l('Facebook Connect (OpenID)');
		$this->description = $this->l('This modules allows Customers to Register & Login with Facebook.');

		$this->_mod_errors = array();
	}

	public function install()
	{
		// TODO: Removed this from install because
		// on reset every time the values were emptied
		// !Configuration::updateValue('FB_CONNECT_APPID', '')
		// !Configuration::updateValue('FB_CONNECT_APPKEY', '')

		if (parent::install() == false ||
			$this->registerHook('DisplayCustomerAccountFormTop') == false ||
			$this->registerHook('DisplayTop') == false ||
			$this->registerHook('DisplayCustomerAccount') == false)
				return false;

		return Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'customer_profile_connect` (
			`id_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_shop` int(11) NOT NULL DEFAULT \'1\',
			`facebook_id` varchar(50) NOT NULL,
			PRIMARY KEY (`id_customer`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1
		');
	}

	public function uninstall()
	{
		//TODO: see if you have to delete the Hook from install function
		if (!parent::uninstall())
			return false;

		// TODO: Should the table be deleted?
		//return Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'customer_profile_connect`');
		return true;
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitFBKey'))
		{
			$fb_connect_appid = (Tools::getValue('fb_connect_appid'));
			if (!$fb_connect_appid)
				$errors[] = $this->l('Invalid Facebook AppID');
			else
				Configuration::updateValue('FB_CONNECT_APPID', $fb_connect_appid);
				
			$fb_connect_appkey = (Tools::getValue('fb_connect_appkey'));
			if (!$fb_connect_appkey)
				$errors[] = $this->l('Invalid Facebook App Key');
			else
				Configuration::updateValue('FB_CONNECT_APPKEY', $fb_connect_appkey);
				
			if (isset($errors) AND sizeof($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<p>'.$this->l('Facebook AppID').'</p><br />
				<label>'.$this->l('Facebook AppID').'</label>
				<div class="margin-form">
					<input type="text" size="20" name="fb_connect_appid" value="'.Tools::getValue('fb_connect_appid', Configuration::get('FB_CONNECT_APPID')).'" />

				</div>

				<p>'.$this->l('Your Facebook App Key').'</p><br />
				<label>'.$this->l('Facebook App Key').'</label>
				<div class="margin-form">
					<input type="text" size="40" name="fb_connect_appkey" value="'.Tools::getValue('fb_connect_appkey', Configuration::get('FB_CONNECT_APPKEY')).'" />

				</div>
				<center><input type="submit" name="submitFBKey" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}

	public function hookDisplayTop($params)
	{
		// Have to do this to destroy FB session at logout
		if (isset($_GET['mylogout']))
		{
			require_once(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php');

			// Create our Application instance (replace this with your appId and secret).
			$facebook = new Facebook(array(
				'appId'  => $fb_connect_appid,
				'secret' => $fb_connect_appkey,
			));

			$facebook->destroySession();
		}

		return '';
	}

	public function hookDisplayCustomerAccount($params)
	{
		$this->context->smarty->assign(array(
			'fbconnect_psb_link' => $this->context->link->getModuleLink('fbconnect_psb', 'link', array(), false, $this->context->language->id)
		));

		return $this->display(__FILE__, 'customer-account.tpl');
	}

	public function hookDisplayCustomerAccountFormTop($params)
	{
		$this->context->smarty->assign(array(
			'fbconnect_psb_reg_link' => $this->context->link->getModuleLink('fbconnect_psb', 'registration', array(), true, $this->context->language->id)
		));

		return $this->display(__FILE__, 'customer-account-form-top.tpl');
	}

	//TODO: find a way to hook to the login (Authentication) page
/*
	public function hookDisplayAuthenticationFormTop($params)
	{
		return 'hookDisplay AuthenticationFormTop';
	}
*/
}