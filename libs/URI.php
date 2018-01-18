<?php

class URI extends ACore 
{
	
	protected $_request;
	protected $_requestArray;
	
	protected function __construct()
	{
		$this->_requestArray = [];
		$this->_request = getFrom('REQUEST_URI', $_SERVER, "");

		if (isString($this->_request)) {
			$this->_requestArray = explode('/', $this->_request);
		}

	}
	
    public function part($num)
	{
		if (!isNumericPositive($num)) Message::exception("Wrong URI part number.(" . $num . ")");

		return getFrom($num, $this->_requestArray);
	}
	
	public function last()
	{
		return end($this->_requestArray);
	}

}
