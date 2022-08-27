<?php

  trait TApplication {

    public function __construct( $parent ) {
      $this->mParent = $parent;
    }

    public function run():void {
      $this->mParent->run();
    }

  }

?>
