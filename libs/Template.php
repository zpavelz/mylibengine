<?php

class Template extends ACore 
{
	
	protected static function _filePath($name, $type)
	{
		$file = $type . "/" . $name . "." . $type . "";
		return Core::loadFile(APPDR . $file, $type) . '?v=' . time();
	}

    public static function load($name, $content = null, $justGet = false)
	{

		if (!isString($name)) Message::exception("Error to load template : wrong name!");
		
		$partPath = 'views/' . $name . '.php';
		$tpl = APPDR . $partPath;

		if (!is_file($tpl)) {
			if ($name == 'error') {
                die('<h1>Error to load "error" template! (' . $partPath . ') </h1>');
            }
			Message::exception("Error to load template : template is not exist! (" . $partPath . ") ");
		}

        if (isObject($content)) $content = get_object_vars($content);
        if (isArray($content)) extract($content, EXTR_SKIP);

		if ($justGet === true) return file_get_contents($tpl);
		return include($tpl);

	}
	
	public static function get($name, $content = null)
	{
		$tpl = self::load($name, $content, true);
		
		if (!isArray($content)) return $tpl;

		foreach ($content as $var => $val) {
			$tpl = str_replace($var, $val, $tpl);
		}
		
		return $tpl;
	}
	
	public static function loadAppCSS($name)
	{
		echo '<link type="text/css" rel="stylesheet" href="/' . self::_filePath($name, 'css') . '" media="all">';
	}
	
	public static function loadAppJS($name)
	{
		echo '<script type="text/javascript" src="/' . self::_filePath($name, 'js') . '" ></script>';
	}

}
