<?php

function isString($str)
{
    return (!empty(trim($str)) && is_string(trim($str)));
}

function isNumeric($num)
{
    return ( (!empty($num) || $num === 0 || $num === '0' || $num === "0") && is_numeric($num));
}

function isNumericPositive($num)
{
    return (isNumeric($num) && $num > 0);
}

function isNumericNegative($num)
{
    return (isNumeric($num) && $num < 0);
}

function isNumericNotPositive($num)
{
    return (isNumeric($num) && $num <= 0);
}

function isNumericNotNegative($num)
{
    return (isNumeric($num) && $num >= 0);
}

function isArray($ar)
{
    return (!empty($ar) && is_array($ar) && count($ar) > 0);
}

function isObject($ob)
{
    return (!empty($ob) && is_object($ob));
}

function i18n($text, $content = [], $lang = 'ru')
{
	if (!isString($text)) Message::exception("I18n : wrong incoming data");
	
	if (isArray($content)) $text = str_replace(array_keys($content), $content, $text);
	
	return Config::getValue($text, 'i18n.' . $lang, $text);
}

function jsonOut($data)
{
	echo json_encode($data); exit(); die();
}

function getFrom($key, $var, $def = null, $r = true)
{
    if ( empty($var) || (!is_array($var) && !is_object($var)) ) return $def;

    if (is_object($var) && isset($var->$key)
        && (!empty($var->$key) || $var->$key === 0 || $var->$key === '0' || $var->$key === "0")) return $var->$key;
    if (is_array($var) && array_key_exists($key, $var)
        && (!empty($var[$key]) || $var[$key] === 0 || $var[$key] === '0' || $var[$key] === "0")) return $var[$key];

    if ($r !== true) return $def;

    foreach ($var as $k => $v) {
        $value = getFrom($key, $v, $def);
        if ((!empty($value) || $value === 0 || $value === '0' || $value === "0")
            && $value !== $def) return $value;
    }

    return $def;
}
