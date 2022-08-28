<?php

  class Logger {

    public function __construct() {  }

    static public function out( $msg ):void {
      echo( "{$msg}" );
    }

  }

?>
