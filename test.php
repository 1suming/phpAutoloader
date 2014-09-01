<?php
define('APP_PATH','./');
require(APP_PATH.'Autoloader.class.php');
Autoloader::setCacheFilePath(dirname(__FILE__).'\tmp\class_path_cached.txt');

Autoloader::setClassPaths(array(
	  APP_PATH.'a/',
	APP_PATH.'b',
));
 
spl_autoload_register(array('Autoloader','loadClass'));
$classA=new ClassA();
$classA->display();
