<?php 

if (!in_array('PrestoChangeoModule', get_declared_classes()))
	require_once(dirname(__FILE__).'/PrestoChangeoModule.php');

if (_PS_VERSION_ < '1.5') {

	if (!in_array('FrontController', get_declared_classes()))
		require_once(dirname(__FILE__).'/FrontController.php');

	// Get out if the context is already defined
	if (!in_array('PrestoChangeoContext', get_declared_classes()))
		require_once(dirname(__FILE__).'/PrestoChangeoContext.php');

	// Get out if the Display (BWDisplay to avoid any conflict)) is already defined
	if (!in_array('BWDisplay', get_declared_classes()))
		require_once(dirname(__FILE__).'/Display.php');

	if (!in_array('ModuleFrontController', get_declared_classes()))
		require_once(dirname(__FILE__).'/ModuleFrontController.php');
}
