<?php

  const DIR_DATA = '../data';
  const DIR_CFG = '../configure';
  const DIR_LIB = './lib';
  const FILE_CONFIG = 'dashcam-geodb.ini';

  require_once( DIR_LIB . '/factory.inc.php' );

  $theApp = Factory::getApplication();
  $theApp->run();

?>
