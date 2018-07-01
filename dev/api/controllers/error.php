<?php
  class Error {
   public function __construct() {
    header('Error', true, 404);
    echo 'Method not found!';
   }
  }
?>