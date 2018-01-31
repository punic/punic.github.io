<?php

function handleError($errno, $errstr, $errfile, $errline)
{
    if ($errno == E_NOTICE || $errno == E_WARNING) {
        throw new Exception("$errstr in $errfile @ line $errline", $errno);
    }
}
set_error_handler('handleError');

define('VENDORDIR', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)).'/vendor');
require_once VENDORDIR.'/autoload.php';
