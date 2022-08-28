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
          ini_set( 'memory_limit', -1 ); /* GML parsing is a memory hog */
          self::$mTheApp = new AppConfigure( self::$mTheApp );
          break;
        default:
          ;
      }
      return self::$mTheApp;
    }

  }

?>
