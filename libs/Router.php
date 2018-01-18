<?php

class Router extends ACore 
{
	
	protected $_route;
	
	public $controller;
	public $action;
	public $attribute;
	
	protected function __construct()
	{
		$this->_route = Config::load('route');
		
		$controller = explode("?", Core::$URI->part(1));
		$action = explode("?", Core::$URI->part(2));
		$attribute = explode("?", Core::$URI->part(3));
		
		$this->controller = $controller[0];
		$this->action = $action[0];
		$this->attribute = $attribute[0];
		
		if (empty($this->action)) $this->action = Config::getValue('action', 'module', 'index');
	}
	
    public function start()
	{
		if ( !isString($this->controller) || !isArray($this->_route) ) return;
		
		foreach ($this->_route as $from => $to) {

			$pathFrom = explode('/', $from);
			$pathTo = explode('/', $to);
			
			if (!isset($pathFrom[1]) || !isString($pathFrom[1])) continue;
				
			if (preg_match('@' . $pathFrom[1] . '@i', $this->controller)) $this->controller = getFrom(1, $pathTo);
			if (empty($this->controller)) $this->controller = Config::getValue('name', 'module');
			
			if (!isset($pathFrom[2]) || !isString($pathFrom[2])) continue;
			
			if (preg_match('@' . $pathFrom[2] . '@i', $this->action)) $this->action = getFrom(2, $pathTo);
			if (empty($this->action)) $this->action = Config::getValue('action', 'module');
			
			if (!isset($pathFrom[3]) || !isString($pathFrom[3])) continue;
			
			if (preg_match('@' . $pathFrom[3] . '@i', $this->attribute)) $this->attribute = getFrom(3, $pathTo);

		}
	
	}
	
	public static function redirect($url = null)
	{
		if (!isString($url)) $url = "http://" . getFrom("HTTP_HOST", $_SERVER) . "/";
		
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: ' . $url);
		echo "<script>location='" . $url . "'</script>";
		exit;
	}
	
	public static function redirectBack()
	{
		return self::redirect(getFrom("HTTP_REFERER", $_SERVER));
	}

}
