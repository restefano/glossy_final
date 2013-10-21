<?php

	// Init
	$sql = array();
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'correios_rate_service_code`;';	
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'correios_cache`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'correios_cache_test`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'correios_rate_config`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'correios_rate_config_service`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'correios_caixas`;';	
	$sql[] = 'DELETE p1.*, p2.*, p3.*, p4.*, p5.*, p6.*
		FROM `'._DB_PREFIX_.'carrier` p1
		LEFT JOIN `'._DB_PREFIX_.'carrier_group` p2 ON p1.`id_carrier` = p2.`id_carrier`
		LEFT JOIN `'._DB_PREFIX_.'carrier_lang` p3 ON p1.`id_carrier` = p3.`id_carrier`
		LEFT JOIN `'._DB_PREFIX_.'carrier_zone` p4 ON p1.`id_carrier` = p4.`id_carrier`
		LEFT JOIN `'._DB_PREFIX_.'cart` p5 ON p1.`id_carrier` = p5.`id_carrier`
		LEFT JOIN `'._DB_PREFIX_.'cart_product` p6 ON p6.`id_cart` = p5.`id_cart`
		WHERE p1.`name`="SEDEX HOJE" OR p1.`name`="SEDEX A COBRAR" OR p1.`name`="SEDEX 10" OR p1.`name`="SEDEX" OR p1.`name`="E-SEDEX" OR p1.`name`="PAC" OR p1.`name`="Sem Frete";';

?>
