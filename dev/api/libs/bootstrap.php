<?php
  class Bootstrap {
   public function __construct() {
    //parse url
    $url = $_GET['url'];
    $url = rtrim($url, '/');
    $url = explode('/', $url);

    $method = $_SERVER['REQUEST_METHOD'];

    //search controller
    $file = 'controllers/'.$url[0].'.php';
    if(file_exists($file)) {
     require $file;
    } else {
     require 'controllers/error.php';
     $controller = new Error();
     return false;
    }
    
    //laungh function
    $controller = new $url[0];
     $controller->$method($url[1]);
   }
  }
?>