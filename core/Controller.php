<?php

class Controller 
{
	protected $_viewLoaded = false;
	protected $_allowLayout = true;

	protected function _loadScripts($scripts)
	{
		if (!isArray($scripts)) return false;
		
		foreach (getFrom('js', $scripts, array()) as $script) {
			Core::$Template->loadAppJS($script);
		}
		foreach (getFrom('css', $scripts, array()) as $script) {
			Core::$Template->loadAppCSS($script);
		}
	}

    public function setLayout($allow)
    {
        if (is_bool($allow)) $this->_allowLayout = $allow;
    }
	
	public function before()
	{
		$this->_viewLoaded = !$this->_allowLayout;
	}
	
	public function indexAction()
	{
	}
	
	public function loadView($name = "index", $content = null)
	{
		if (!isString($name)) Message::error("Can`t load view : name is not string or empty");
		
		Template::load(Core::$module . "/" . $name, $content);
		$this->_viewLoaded = true;
	}
	
	public function cache_clearAction()
	{
		Core::clearCache();
		Router::redirectBack();
	}
	
	public function after()
	{
		if ($this->_allowLayout) {
			Core::$model->checkMessages();
            Template::load('header');
            $this->_loadScripts(Config::getValue('before', 'template'));
            Template::load('core_messages');
		}
		if ($this->_viewLoaded == false && $this->_allowLayout) $this->loadView(Core::$Router->action, $this);
        if ($this->_allowLayout) {
            Template::load('footer');
            $this->_loadScripts(Config::getValue('after', 'template'));
        }
	}
	
	public function requestGet()
	{
		return isArray($_GET);
	}
	
	public function requestPost()
	{
		return isArray($_POST);
	}

	public function requestFiles()
	{
		return isArray($_FILES);
	}
	
	public function getRequest($key)
	{
		if (!isString($key)) return null;
		
		$post = getFrom($key, $_POST, null);
		$get  = getFrom($key, $_GET, null);
		$files  = getFrom($key, $_FILES, null);
		
		if (!empty($post) && !empty($get) && !empty($files)) {
			$result = new stdClass();
			$result->post = $post;
			$result->get = $get;
			$result->files = $files;
            return $result;
		} else if (!empty($post) && !empty($get)) {
			$result = new stdClass();
			$result->post = $post;
			$result->get = $get;
            return $result;
		} else if (!empty($post)) {
			return $post;
		} else if (!empty($get)) {
			return $get;
		} else if (!empty($files)) {
			return $files;
		}
		
		return null;
	}
	
}
