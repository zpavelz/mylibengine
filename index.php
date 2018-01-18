<?php
define('DR', $_SERVER['DOCUMENT_ROOT'] . "/");
define('SYSDR', DR . "../");

include(SYSDR . '/core/Helper.php');
include(SYSDR . '/core/ACore.php');
include(SYSDR . '/core/Core.php');

Core::loadLib('Message');
Core::loadLib('Config');

define('APPDR',  SYSDR . "apps/" . Core::getAppName() . "/");

Core::loadLibs(['URI','Router','Template','MyDate']);
Core::load();

define('STARTTIME', MyDate::getMicrotime());
define('STARTMEMORYUSAGE', Core::getMemoryUsage());

Core::includeFile(SYSDR . '/core/Controller.php');
Core::includeFile(SYSDR . '/core/Model.php');

Core::includeFileIfExists(APPDR . 'app_core/App_Core.php');
Core::includeFileIfExists(APPDR . 'app_core/App_Controller.php');
Core::includeFileIfExists(APPDR . 'app_core/App_Model.php');

try {
	Core::includeFile(SYSDR . '/header.php');
	Core::includeFile(SYSDR . '/footer.php');
}
catch( Exception $ex) {
	Message::error($ex->getMessage());
}
