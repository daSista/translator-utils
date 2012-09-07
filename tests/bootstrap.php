<?php
include dirname(__FILE__) . '/../vendor/autoload.php';

define("APPLICATION_PATH", realpath(dirname(__FILE__) . '/../../') . '/');

$loader = new \Mockery\Loader;
$loader->register();
