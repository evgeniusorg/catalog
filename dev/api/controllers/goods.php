<?php
  class goods {
    public function GET($urlData, $formData) {
      global $mysqli;
      global $mc;
      
      //check params
      if (
        !isset($formData["limit"]) OR 
        !isset($formData["offset"]) OR 
        !isset($formData["sorting"]) OR 
        !isset($formData["order"]) OR
        !isset($formData["lastid"])
      ) {
        error(400, "Request has not all params", $formData);
        return;
      }

      $formData["limit"] = intval($formData["limit"]);
      $formData["offset"] = floatval($formData["offset"]);
      $formData["lastid"] = intval($formData["lastid"]);

      if (
        $formData["lastid"] < 0 OR
        $formData["limit"] <= 0 OR
        $formData["offset"] < 0 OR

        !in_array($formData["sorting"], array("id", "price")) OR
        !in_array($formData["order"], array("asc", "desc")) 
      ) {
        error(400, "Request has not valid params", $formData);
        return;
      }

      $sorting = $formData["sorting"];
      $order = $formData["order"];
      $offset = $formData["offset"];
      $limit = $formData["limit"];
      $lastId = $formData["lastid"];

      $data = array();
      $data["total"] = 0;
      $data["goods"] = array();

      //check data in memcached
      if ($goods = memcache_get($mc, "goods_".$sorting)) {  
        $data["total"] = count($goods);

        //select goods from range
        $data["goods"] = $this->FILTER_OF_GOODS($goods, $sorting, $order, $offset, $lastId, $limit);
      }
      else {
        //load data from db
        $sql = "SELECT id, price FROM goods ORDER BY ";
        if ($sorting == "price") {
          $sql .= "price $order, ";
        }
        $sql .= "id $order";
  
        $searchGoods = $mysqli->query($sql);
        if ($mysqli->errno) {
          error(400, "Goods not found", "Select Error (" . $mysqli->errno . ") " . $mysqli->error);
        }
        else{   
          $mcGoods = array();
          while ($row = $searchGoods->fetch_assoc()) {
            $row["id"] = intval($row["id"]);
            $row["price"] = floatval($row["price"]);
            $mcGoods[] = $row;
          }

          //set data to memcached
          memcache_set($mc, "goods_".$sorting, $mcGoods, MEMCACHE_COMPRESSED, 60*60);

          //select goods from range
          $data["goods"] = $this->FILTER_OF_GOODS($mcGoods, $sorting, $order, $offset, $lastId, $limit);
          
          $data["total"] = count($mcGoods);
        }

        $data["offset"] = $offset;
        $data["limit"] = $limit;
        $data["sorting"] = $sorting;
        $data["order"] = $order;
        $data["lastid"] = $lastId;
      }

      echo json_encode($data);
    }

    //create good
    public function POST($urlData, $formData) {
      global $mysqli;
      global $mc;

     //check params
      if (
        !isset($formData["title"]) OR 
        !isset($formData["description"]) OR 
        !isset($formData["price"]) OR
        !isset($formData["img"]) OR

        !is_numeric($formData["price"]) OR
        $formData["price"] <= 0 OR
        $formData["price"] > 10000000000 OR

        strlen($formData["title"]) > 100 OR
        strlen($formData["title"]) == 0 OR

        strlen($formData["description"]) > 1000 OR
        strlen($formData["description"]) == 0

      ) {
        error(400, "Request has not valid data", $formData);
        return;
      }

      $title = $mysqli->real_escape_string($formData["title"]);
      $description = $mysqli->real_escape_string($formData["description"]);
      $imgData = $mysqli->real_escape_string($formData["img"]);
      $price = $formData["price"];

      $imgUrl = imageSave($imgData);

      $sql = "INSERT INTO goods (title, description, price, img) VALUES ('$title', '$description', '$price', '$imgUrl')";

      $addGood = $mysqli->query($sql);
      if ($mysqli->errno) {
        error(400, "Goods not saved", "Select Error (" . $mysqli->errno . ") " . $mysqli->error);
      }
      else{ 
        $id = intval($mysqli->insert_id);
        $good = array(  
          "id" => $id,
          "title" => $title,
          "price" => floatval($price),
          "description" => $description,
          "img" => $imgUrl
        );

        //set good to memcached
        memcache_set($mc, "good_id_".$id, $good, MEMCACHE_COMPRESSED, 60*60);
        $this->ADD_GOOD_TO_MC("id", $good);
        $this->ADD_GOOD_TO_MC("price", $good);

        echo json_encode($good);
      }
    }

    //update good
    public function PUT($urlData, $formData) {
      global $mysqli;
      global $mc;

      $id = $urlData[0];
      //check params
      if (
        !isset($id) OR 
        !is_numeric($id) OR
        $id <= 0 OR

        !isset($formData["title"]) OR 
        !isset($formData["description"]) OR 
        !isset($formData["price"]) OR
        !isset($formData["img"]) OR

        !is_numeric($formData["price"]) OR
        $formData["price"] <= 0 OR
        $formData["price"] > 10000000000 OR

        strlen($formData["title"]) > 100 OR
        strlen($formData["title"]) == 0 OR

        strlen($formData["description"]) > 1000 OR
        strlen($formData["description"]) == 0

      ) {
        error(400, "Request has not valid data", $formData);
        return;
      }

      $title = $mysqli->real_escape_string($formData["title"]);
      $description = $mysqli->real_escape_string($formData["description"]);
      $imgData = $mysqli->real_escape_string($formData["img"]);
      $price = $formData["price"];

      $imgUrl = imageSave($imgData);

      $sql = "UPDATE goods SET title='$title', description='$description', price='$price', img='$imgUrl' WHERE id=$id";

      $updateGood = $mysqli->query($sql);
      if ($mysqli->errno) {
        error(400, "Goods not updated", "Select Error (" . $mysqli->errno . ") " . $mysqli->error);
      }
      else{    
        $good = array(  
          "id" => intval($id),
          "title" => $title,
          "price" => floatval($price),
          "description" => $description,
          "img" => $imgUrl
        );

        //update good in memcached
        memcache_set($mc, "good_id_".$id, $good, MEMCACHE_COMPRESSED, 60*60);
        $this->UPDATE_GOOOD_IN_MC($good);

        echo json_encode($good);
      }
    }

    //delete good
    public function DELETE($urlData, $formData) {
      global $mysqli;
      global $mc;

      $id = $urlData[0];

      //check params
      if (
        !isset($id) OR
        !is_numeric($id) OR
        $id <= 0
      ) {
        error(400, "Request has not valid data", $urlData);
        return;
      }

      //delete good
      $sql = "DELETE FROM goods WHERE id=$id";

      $deleteGood = $mysqli->query($sql);
      if ($mysqli->errno) {
        error(400, "Goods not deleted", "Select Error (" . $mysqli->errno . ") " . $mysqli->error);
      }
      else{
        //delete good from memcached
        memcache_delete($mc, "good_id_".$id);
        $this->DELETE_GOOD_FROM_MC("id", $id);
        $this->DELETE_GOOD_FROM_MC("price", $id);

        echo json_encode(array("message"=>"Good $id was deleted"));
      }
    }

    //load good info
    private function GET_GOOD($id){
      global $mysqli;
      global $mc;

      if ($good = memcache_get($mc, "good_id_".$id)){
        return $good;
      }
      else{
        $sql = "SELECT * FROM goods WHERE id=$id";

        $searchGood = $mysqli->query($sql);
        if ($mysqli->errno) {
          error(400, "Good not found", "Select Error (" . $mysqli->errno . ") " . $mysqli->error);
        }
        else{  
          $good = $searchGood->fetch_array(MYSQL_ASSOC);
          $good["id"] = intval($good["id"]);
          $good["price"] = floatval($good["price"]);
          memcache_set($mc, "good_id_".$id, $good, MEMCACHE_COMPRESSED, 60*60);
          return $good;
        } 
      }
    }

    //delete good id from sorted list in memcached
    private function DELETE_GOOD_FROM_MC($sorting, $id){  
      global $mc;

      if ($goods = memcache_get($mc, "goods_".$sorting)){
        $newList = array();
        foreach ($goods as $good) {
          if (intval($id) != $good["id"]) {
            $newList[] = $good;
          }
        }
        memcache_set($mc, "goods_".$sorting, $newList, MEMCACHE_COMPRESSED, 60*60);
      }
      return false;
    }

    //add good id to sorted list in memcached
    private function ADD_GOOD_TO_MC($sorting, $newGood){
      global $mc;

      if ($goods = memcache_get($mc, "goods_".$sorting)){
        $newList = array();
        
        foreach ($goods as $index => $good) {
          if ($index == 0 && $newGood[$sorting] < $good[$sorting]){
            $newList[] = array("id" => $newGood["id"], "price" => $newGood["price"]);
            $newList[] = $good;
          }
          else if ($good[$sorting] <= $newGood[$sorting] && isset($goods[$index + 1]) == false){
            $newList[] = $good;
            $newList[] = array("id" => $newGood["id"], "price" => $newGood["price"]);
          }
          else if ($good[$sorting] <= $newGood[$sorting] && $newGood[$sorting] < $goods[$index + 1][$sorting]){
            $newList[] = $good;
            $newList[] = array("id" => $newGood["id"], "price" => $newGood["price"]);
          }
          else {
            $newList[] = $good;
          }
        }

        if (count($goods) == 0 ) {
          $newList[] = array("id" => $newGood["id"], "price" => $newGood["price"]);
        }

        memcache_set($mc, "goods_".$sorting, $newList, MEMCACHE_COMPRESSED, 60*60);
      }
      return false;
    }

    //update sorted list by price in memcached
    private function UPDATE_GOOOD_IN_MC($newGood){
      global $mc;

      $id = $newGood["id"];
      $oldGood = memcache_get($mc, "good_id_".$id);

      if ( $oldGood && $oldGood["price"] != $newGood["price"] && $goods = memcache_get($mc, "goods_price") ){
        $this->DELETE_GOOD_FROM_MC("price", $id);
        $this->ADD_GOOD_TO_MC("price", $newGood);
      }
      return false;
    }

    private function FILTER_OF_GOODS($fullGoods, $sorting, $order, $offset, $lastId, $limit){
      $selectGoods = array();

      foreach ($fullGoods as $good) {
        if ($sorting == "id" AND $order == 'asc' && $good["id"] > $offset) {
          $selectGoods[] = $this->GET_GOOD($good["id"]); 
        }
        else if ($sorting == "id" AND $order == 'desc' AND $offset > 0 AND $good["id"] < $offset) {
          $selectGoods[] = $this->GET_GOOD($good["id"]); 
        }
        else if ($sorting == "id" AND $order == 'desc' AND $offset == 0) {
          $selectGoods[] = $this->GET_GOOD($good["id"]); 
        }
        else if ($sorting == "price" AND $order == 'asc' AND $good["price"] > $offset) {
          $selectGoods[] = $this->GET_GOOD($good["id"]); 
        }
        else if ($sorting == "price" AND $order == 'desc' AND $offset > 0 AND $lastId > 0 AND $good["price"] < $offset AND $good["id"] < $lastId) {
          $selectGoods[] = $this->GET_GOOD($good["id"]); 
        }
        else if ($sorting == "price" AND $order == 'desc' AND $offset > 0 AND $lastId == 0 AND $good["price"] < $offset) {
          $selectGoods[] = $this->GET_GOOD($good["id"]); 
        }
        else if ($sorting == "price" AND $order == 'desc' AND $offset == 0){
          $selectGoods[] = $this->GET_GOOD($good["id"]); 
        }     

        if (count($selectGoods) >= $limit) {
          break;
        }
      }

      return $selectGoods;
    }
  }
?>