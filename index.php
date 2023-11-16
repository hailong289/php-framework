<?php
require 'vendor/autoload.php';
session_start();
use App\App;
try {
    require_once "bootstrap.php";
    $app = new App;
}catch (Throwable $e) {
    $code = (int)$e->getCode();
    http_response_code($code ? $code:500);
    if(DEBUG_LOG) {
        $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
        if (!file_exists(__DIR__ROOT .'/storage')) {
            if (!mkdir($concurrentDirectory = __DIR__ROOT . '/storage', 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $e, FILE_APPEND);
    }
    throw $e;
}
