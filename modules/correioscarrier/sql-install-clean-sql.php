<?php

	// Init
	$sql2 = array();

	// Clear related tables... It's best to use since I noticed problems earlier with too many carriers listed!
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'carrier` WHERE `id_carrier` != "";';
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'carrier_group` WHERE `id_carrier` != "";';
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'carrier_zone` WHERE `id_carrier` != "";';
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'carrier_lang` WHERE `id_carrier` != "";';
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'delivery` WHERE `id_carrier` != "";';
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'range_price` WHERE `id_carrier` != "";';
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'range_weight` WHERE `id_carrier` != "";';
	
	/*
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'cart` WHERE `id_cart` != "";';
	$sql2[] = 'DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` != "";';
	*/

?>