<?php

class Message extends ACore 
{
	
    const KEY_SESSION = "core_msg";
    const TYPE_ERROR = "error";
    const TYPE_SUCCESS = "success";
    const TYPE_GENERAL = "general";
    
	protected static $_list;

	public static function getTypes()
    {
        return [
            self::TYPE_ERROR,
            self::TYPE_SUCCESS,
            self::TYPE_GENERAL,
        ];
    }
	
	public static function exception($text)
    {
        $text = (isString($text)) ? $text : "";
        $otherErrors = getFrom(self::TYPE_ERROR, self::$_list);
        try
        {
            throw new Exception($text . " " . ( (isArray($otherErrors)) ? " | " . implode(" | ", $otherErrors) : "") );
        }
        catch ( Exception $ex)
        {
            self::error($ex->getMessage());
        }
    }

    public static function wrongData()
    {
        self::exception(" Wrong incoming data! ");
    }

    public static function getAll()
    {
        $messages = getFrom(self::KEY_SESSION, $_SESSION);
        $_SESSION[self::KEY_SESSION] = null;
        return $messages;
    }

    public static function getList()
    {
        return self::$_list;
    }

    public static function error($text)
    {
        if (!isString($text)) self::exception('Wrong text content for error.');

        Core::$Template->load(self::TYPE_ERROR, ['text' => $text]);
        exit();
    }

    public static function get($name = self::TYPE_SUCCESS)
    {
        if (!isString($name)) self::wrongData();

        $msg = getFrom($name, getFrom(self::KEY_SESSION, $_SESSION));

        if (!isArray($msg)) return null;

        unset($_SESSION[self::KEY_SESSION][$name]);
        return implode(' | ', $msg);

    }

    public static function set($msg, $type = self::TYPE_GENERAL)
    {
        if (!isString($msg)) self::wrongData();
        if (!in_array($type, self::getTypes())) $type = self::TYPE_GENERAL;
        if (!array_key_exists($type, self::$_list)) self::$_list[$type] = [];

        self::$_list[$type][] = $msg;
    }

    public static function check()
    {
        $msg = self::getAll();

        if (!isArray($msg)) return;

        foreach($msg as $type => $messages) {
            self::$_list[$type] = $messages;
        }
    }

    public static function count()
    {
        return (int)count(self::$_list);
    }
	
}
