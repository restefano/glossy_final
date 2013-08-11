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

class FBConnect_PSBLinkModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	public $ssl = true;
 
	/**
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		parent::initContent();
 
		if (!$this->context->customer->isLogged())
		{
			$back = $this->context->link->getModuleLink('fbconnect_psb', 'link', array(), TRUE, $this->context->language->id);
			Tools::redirect('index.php?controller=authentication&back='.urlencode($back));

		}

		$fb_connect_appid = (Configuration::get('FB_CONNECT_APPID'));
		$fb_connect_appkey = (Configuration::get('FB_CONNECT_APPKEY'));

		require_once(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php');

		// Create our Application instance (replace this with your appId and secret).
		$facebook = new Facebook(array(
			'appId'  => $fb_connect_appid,
			'secret' => $fb_connect_appkey,
		));

		// Get User ID
		$user = $facebook->getUser();

		if ($user)
		{
			try {
				// Proceed knowing you have a logged in user who's authenticated.
				$fb_user_profile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
			}
		}

		// current user state Logged In with FB
		if ($user)
		{
			$sql = 'SELECT `id_customer`
				FROM `'._DB_PREFIX_.'customer_profile_connect`
				WHERE `facebook_id` = \''.(int)$fb_user_profile['id'].'\'';
			$customer_id = Db::getInstance()->getValue($sql);

			if ($customer_id != $this->context->customer->id)
			{
				$this->context->smarty->assign(array(
					'fbconnect_psb_status' => 'error',
					'fbconnect_psb_massage' => 'The Facebook account is already linked to another account.',
					'fbconnect_psb_fb_picture' => 'https://graph.facebook.com/'.$fb_user_profile['username'].'/picture',
					'fbconnect_psb_fb_name' => $fb_user_profile['name']
				));
			}
			else if ($customer_id == $this->context->customer->id)
			{
				$this->context->smarty->assign(array(
					'fbconnect_psb_status' => 'linked',
					'fbconnect_psb_massage' => 'The Facebook account is already linked to your account.',
					'fbconnect_psb_fb_picture' => 'https://graph.facebook.com/'.$fb_user_profile['username'].'/picture',
					'fbconnect_psb_fb_name' => $fb_user_profile['name']
				));
			}
			else if($fb_user_profile['email'] == $this->context->customer->email)
			{
				if(Db::getInstance()->insert('customer_profile_connect',array( 'id_customer' => (int)$this->context->customer->id, 'facebook_id' => (int)$fb_user_profile['id'])))
				{
					$this->context->smarty->assign(array(
						'fbconnect_psb_status' => 'conform',
						'fbconnect_psb_massage' => 'Your Facebook account has been linked to account.',
						'fbconnect_psb_fb_picture' => 'https://graph.facebook.com/'.$fb_user_profile['username'].'/picture',
						'fbconnect_psb_fb_name' => $fb_user_profile['name']
					));
				}
			}
			else if($fb_user_profile['email'] != $this->context->customer->email)
			{
				$this->context->smarty->assign(array(
					'fbconnect_psb_status' => 'error',
					'fbconnect_psb_massage' => 'Your email address on files is not the same as Facebook.'.$customer_id,
				));
			}
		}
		else
		{
			$this->context->smarty->assign(array(
				'fbconnect_psb_status' => 'login',
				'fbconnect_psb_massage' => 'You must login to your Facebook account.',
				'fbconnect_psb_loginURL' => $facebook->getLoginUrl(),
			));
		}

		$this->setTemplate('link_fb.tpl');
	}
}