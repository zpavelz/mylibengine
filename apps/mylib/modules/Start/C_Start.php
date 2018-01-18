<?php

class Controller_Start extends App_Controller
{
    public function before()
    {
        if ($this->requestPost() || $this->requestFiles()) $this->setLayout(false);
        parent::before();
    }

	public function indexAction()
    {

    }
}
