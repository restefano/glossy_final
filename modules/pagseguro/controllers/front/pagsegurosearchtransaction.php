<?php
/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class PagSeguro Search Transaction
 */
class PagSeguroSearchTransaction
{
	private $transaction_code;
	private $obj_credential;
	private $obj_transaction;

	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->transaction_code = (isset($_POST['notificationCode']) && trim($_POST['notificationCode']) !== '' ? trim($_POST['notificationCode']) : null);
		$this->_createCredential();
		$this->_createTransaction();
	}

	/**
	 * Create Credential
	 */
	private function _createCredential()
	{
		$this->obj_credential = new PagSeguroAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'), Configuration::get('PAGSEGURO_TOKEN'));
	}

	/**
	 * Create Transaction
	 */
	private function _createTransaction()
	{
		$this->obj_transaction = PagSeguroTransactionSearchService::searchByCode($this->obj_credential, $this->transaction_code);
	}
}
