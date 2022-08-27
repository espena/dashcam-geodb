<?php

  require_once( DIR_LIB . '/i_application.inc.php' );
  require_once( DIR_LIB . '/app_base.inc.php' );
  require_once( DIR_LIB . '/app_configure.inc.php' );

  class Factory {

    private static IApplication $mTheApp;

    public static function getApplication():IApplication {
      self::$mTheApp = new AppBase();
      switch( $_SERVER[ 'SCRIPT_FILENAME' ] ) {
        case 'configure.php':
          self::$mTheApp = new AppConfigure( self::$mTheApp );
          break;
        default:
          ;
      }
      return self::$mTheApp;
    }

  }

?>
