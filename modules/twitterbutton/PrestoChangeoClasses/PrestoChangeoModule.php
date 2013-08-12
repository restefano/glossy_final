<?php 
/**
 * Base module class of Presta Changeo Module
 */

class PrestoChangeoModule extends Module 
{
	protected $context = '';
	public    $smarty  = '';
	protected $_last_updated;
	protected $_full_version;	
	
	/**
	 * Add to contructor instance of context  
	 */
	public function __construct()
	{
		parent::__construct();
		if ($this->getPSV() < 1.5) { 
			$this->context = PrestoChangeoContext::getContext();
			$this->smarty = $this->context->smarty;
		}
	}

	/**
	 * Get context 
	 */
	public function getContext() 
	{
		return $this->context;
	}

	/**
	 * get version of PrestaShop 
	 * return float value version 
	 */
	protected function getPSV()
	{
		return floatval(substr($this->getRawPSV(),0,3));
	}

	/**
	 * get raw version of PrestaShop 
	 */	
	private function getRawPSV()
	{
		return _PS_VERSION_;
	} 

	/**
	 * Compare version of prestashop curent with $version2
	 *  
	 */
	protected function comparePSV($operator, $version2)
	{
		return version_compare($this->getRawPSV(), $version2, $operator);
	}

	/*
	 * Check if override files were properly copied.
	 */
	protected function overrideCheck($mod, $srv)
	{
		if (!is_array($srv))
			return false;
		$class_found = false;
		foreach ($mod as $row)
		{
			if (!$class_found)
			{
				if (substr($row,0,5) == 'class')
				{
					$class_found = true;
					//print "Class found<br />";
				}
				continue;
			}
			else
			{
				$row = trim($row);
				$row_found = false;
				foreach ($srv as $key => $orow)
				{
					if ($row == trim($orow))
					{
						$srv = array_slice($srv, $key);
						$row_found = true;
						//print "Found $row<br />";
						break;
					}
				}
				if (!$row_found)
				{
					//print "Not Found $row<br />";
					return false;
				}
			}
		}
		return true;
	}
	
	
	/**
	 *  Does module need updating
	 */
	protected function upgradeCheck($module)
	{
		// Only run upgrae check if module is loaded in the backoffice.
		if (($this->getPSV() > 1.1  && $this->getPSV() < 1.5) && (!is_object($this->context->cookie) || !$this->context->cookie->isLoggedBack()))
			return;
		if ($this->getPSV() >= 1.5)
		{
			if (!isset($this->context->employee) || !$this->context->employee->isLoggedBack())
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