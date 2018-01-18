<?php

class Model
{
	
	protected function _filePath($name, $type)
	{
		$file = "views/" . Core::$module . "/" . $type . "/" . $name . "." . $type . "";
		return Core::loadFile(APPDR . $file, $type) . '?v=' . time();
	}
	
	public function loadCSS($name)
	{
		echo '<link type="text/css" rel="stylesheet" href="/' . $this->_filePath($name, 'css') . '" media="all">';
	}
	
	public function loadJS($name)
	{
		echo '<script type="text/javascript" src="/' . $this->_filePath($name, 'js') . '" ></script>';
	}
	
	public function checkMessages()
	{
		Message::check();
	}
	
}
