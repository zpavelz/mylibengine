<?php

class Config 
{
	
	/**
     * Get config array
     * @param string $name config name
     * @return array
     * */
    public static function load($name)
    {
		if (!isString($name)) return [];
		
		$names = explode(".", $name);
        $data = [];
        if (defined('APPDR')) $data = Core::includeFile(APPDR . "config/" . $names[0] . ".php");
        if (empty($data)) $data = Core::includeFile(SYSDR . "config/" . $names[0] . ".php");

		if (count($names) < 2) return $data;
		
		unset($names[0]);
		foreach ($names as $n) {
			$tmp = getFrom($n, $data);
			if (isArray($tmp)) $data = $tmp;
		}
		
		return $data;
	}
	
	/**
	 * Get config value
	 * @param string $key value name
	 * @param string $name config name
	 * @param void $default default value if desired value is not found
	 * @return string
	 * */
	public static function getValue($key, $name, $default = null)
	{
		if (!isString($key) || !isString($name)) return $default;
		
		$value = self::load($name);
		
		if (!isArray($value)) return $default;
		
		return getFrom($key, $value, $default);
	}
	
}
