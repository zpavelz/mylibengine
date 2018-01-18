<?php

class Core extends ACore
{
	
	public static $DB;
	public static $URI;
	public static $Router;
	public static $Template;
	public static $module;
	public static $controller;
	public static $model;

    public static function load()
    {
        self::loadLibsFromConfig();

        self::$Template = Template::obj();
        self::$URI = URI::obj();
        self::$Router = Router::obj();

        if (class_exists("DB"))
            self::$DB = DB::obj();
    }
	
	public static function loadLib($name)
	{
		if (!isString($name)) Message::exception("Error to load lib : wrong lib name!");
		
		return self::includeFile(SYSDR . "/libs/" . $name . ".php");
	}
	
	public static function loadLibsFromConfig()
	{
		foreach (Config::load('libs') as $lib => $isAllow) {
			if ($isAllow) self::loadLib($lib);
		}
	}

	public static function loadLibs(array $libs)
    {
        foreach ($libs as $lib) self::loadLib($lib);
    }
	
	public static function loadModule($module = null)
	{
		if (empty($module)) $module = self::$Router->controller;
		if (!isString($module)) $module = Config::getValue('name', 'module');
		if (!isString($module)) Message::exception("Error to load module : wrong name " . $module);

		$module = ucfirst($module);

		self::$module = $module;
		self::$model = self::loadModel($module);
		self::$controller = self::loadController($module);

		return true;
	}

	public static function loadController($controller = null)
	{
		if (!isString($controller)) Message::exception("Error to load controller : wrong name " . $controller . "");

		$controller = ucfirst($controller);
		$controllerFile = APPDR . "modules/" . $controller . "/C_" . $controller . ".php";

		if (!is_file($controllerFile)) {
			return (class_exists('App_Controller')) ? new App_Controller() : new Controller();
		}

		self::includeFile($controllerFile);

		$controller = "Controller_" . $controller;

		return new $controller();
	}

	public static function loadModel($model)
	{
		if (!isString($model)) Message::exception("Error to load model : wrong name " . $model . "");

		$model = ucfirst($model);
		$modelFile = APPDR . "modules/" . $model . "/M_" . $model . ".php";

		if (!is_file($modelFile)) {
			return (class_exists('App_Model')) ? new App_Model() : new Model();
		}

		self::includeFile($modelFile);

		$model = "Model_" . $model;

		return new $model();
	}

	public static function renderPage()
	{
        $controller = self::$Router->controller;
		$action = self::$Router->action;
		$attribute = self::$Router->attribute;

		self::loadModule($controller);
		$action = $action . 'Action';

		if (!method_exists(self::$controller, $action)) {
			Message::exception("Page /" . $controller . "/" . $action . " not exist!");
		} else {
			self::$controller->before();
			self::$controller->$action($attribute);
			self::$controller->after();
		}
	}

	public static function cacheShowPage()
	{
		ob_start();

		self::renderPage();
		self::setPageToCache(ob_get_contents());

		ob_end_clean();

		self::getPageFromCache();
	}

	public static function clearDir($directory)
	{
		if (!isString($directory) || !is_dir($directory)) Message::exception("Error to clear directory " . $directory);

		$dir = dir($directory);

		while($file = $dir->read()) {

			if (is_file($directory."/".$file)) {

				unlink($directory."/".$file);

			} else if ( is_dir($directory."/".$file) && $file != "." && $file != "..") {
				self::clearDir($directory."/".$file);
				rmdir($directory."/".$file);
			}
		}

		$dir->close();
		return true;
	}

	public static function clearCache()
	{
		return self::clearDir(DR . "cache");
	}

	/**
	 * Set session message
	 * @param string $value message value
	 * @param string $name message name
	 * */
	public static function setMessage($value, $name = 'success')
	{
		if (!isString($name) || !isString($value)) Message::wrongData();
		$_SESSION['core_msg'][$name][] = $value;
	}

	public static function arrayTree($array, $parentId = 1, $parentIdKey = 'parent_id', $idKey = 'id', $childrensKey = 'childrens')
    {
		$arrayTree = [];

		if (!isArray($array[0]) || !isNumericPositive($parentId)) return $arrayTree;
		if (!isString($idKey) || !isString($parentIdKey)) return $arrayTree;

		foreach ($array as $k => $section) {

			if (!isArray($section)) continue;

			if ($section[$parentIdKey]==$parentId) {

				$arrayTree[$k] = $section;
				$arrayTree[$k][$childrensKey] = self::arrayTree($array, $section[$idKey], $parentIdKey, $idKey, $childrensKey);
			}
		}

		return $arrayTree;
	}

	public static function getDefaultCachePageName()
	{
		return Config::getValue('cache_path', 'site', "cache/page/") . md5(self::$Router->controller . self::$Router->action . self::$Router->attribute) . ".php";
	}
	
	public static function getPageFromCache($cacheName = null)
	{
		if (!isString($cacheName)) $cacheName = self::getDefaultCachePageName();
		if (is_file(DR . $cacheName)) return self::includeFile(DR . $cacheName);
		return false;
	}
	
	public static function setPageToCache($cacheFileContent, $cacheName = null)
	{
		if (!isString($cacheName)) $cacheName = self::getDefaultCachePageName();
		if (is_file(DR . $cacheName)) return true;
		
		@mkdir(DR . 'cache/page');
		$cacheFile = fopen(DR . $cacheName, "w");
		fwrite($cacheFile, $cacheFileContent);
		fclose($cacheFile);
		
		return true;
	}
    
	public static function includeFileIfExists($file)
	{
		return self::includeFile($file, false);
	}
	
	public static function includeFile($file, $necessarily = true)
	{
		if (!isString($file)) return Message::exception("Wrong file name on include method!");
		
		if (!file_exists($file)) {
			if ($necessarily) return Message::exception("File '" . $file . "' not exist on include method!");
			return false;
		}
		
		return include($file);
	}
	
	public static function loadFile($file, $type)
	{
		if (!isString($file)) Message::exception("Error to load file : wrong name " . $file . " ");
		if (!isString($type)) Message::exception("Error to load file : wrong type " . $type . " ");
        if (!is_file($file)) Message::exception("Error to load file : file not exist " . $file . " ");
		
		$cacheName = "cache/" . $type . "/" . md5($file) . "." . $type;

		if (!is_file(DR . $cacheName)) {
			
			@mkdir(DR . 'cache');
			@mkdir(DR . 'cache/' . $type);
			$cacheFile = fopen(DR . $cacheName, "w");
			fwrite($cacheFile, file_get_contents($file));
			fclose($cacheFile);
			
		}
		
		return $cacheName;
	}

	public static function getExecutionMemoryUsage()
	{
		return self::getMemoryUsage() - STARTMEMORYUSAGE;
	}
	
	public static function getMemoryUsage()
	{
		return (int)(memory_get_peak_usage(true)/1024);
	}

    public static function getAppName()
    {
        $appRoute = Config::load('apps.route');
        if (empty($appRoute)) Message::error("apps.route config is empty");

        $appName = getFrom(getFrom('HTTP_HOST', $_SERVER, null), $appRoute);
        if (empty($appName)) Message::error("No app has been found");

        if (!is_dir(SYSDR . "apps/" . $appName)) Message::error("App " . $appName . " has not been found");

        return $appName;
    }
	
}


