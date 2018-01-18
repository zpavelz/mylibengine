<?php 

date_default_timezone_set(Config::getValue('timezone', 'site'));
session_start();
header('Content-Type: text/html; charset=' . Config::getValue('charset', 'site'));

Core::$Router->start();

if (Config::getValue('cache_enabled', 'site')) {
	if (!Core::getPageFromCache()) Core::cacheShowPage();
} else {
	Core::renderPage();
}
