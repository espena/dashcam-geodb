<?php

  const DIR_CFG = '../configure';
  const DIR_LIB = './lib';

  require_once( DIR_LIB . '/factory.inc.php' );
  $theApp = Factory::getApplication();
  $theApp->run();

?>
