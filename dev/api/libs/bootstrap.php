<?php
  class Bootstrap {
    public function __construct() {
      //parse url
      $url = $_GET["url"];
      $url = rtrim($url, "/");
      $url = explode("/", $url);

      //rapse data
      $method = $_SERVER["REQUEST_METHOD"];
      $formData = $this->getFormData($method);

      $router = $url[0];
      $urlData = array_slice($url, 1);

      //search controller
      $file = "controllers/".$router.".php";
      if(file_exists($file)) {
        require $file;
      } else {
        error(404, "Method not found!", $_GET["url"]);
        return false;
      }
      
      //laungh function
      $controller = new $router;
      $controller->$method($urlData, $formData);
    }

    public function getFormData($method){
      //parse data for GET request
      if ($method === "GET") {
        return $_GET;
      } else {
        //parse data for POST, PUT, DELETE requests (json format)
        return json_decode(file_get_contents("php://input"), true);
      }
    }
  }
?>