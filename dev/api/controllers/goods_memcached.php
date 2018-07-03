<?php
  $mc = memcache_connect('localhost', 11211);

  class goods {
    public function GET($arg = false) {
      global $mysqli;

      //check params
      if (!isset($_GET['limit']) OR 
        !isset($_GET['offset']) OR 
        !isset($_GET['sorting']) OR
        !isset($_GET['order']) 
      ) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid params"));
        return;
      }

      //parse params
      $limit = $mysqli->real_escape_string($_GET['limit']);
      $sorting = $mysqli->real_escape_string($_GET['sorting']);
      $offset = $mysqli->real_escape_string($_GET['offset']);
      $order = $mysqli->real_escape_string($_GET['order']); 

      //check data in memcached
      if ($goods = memcache_get($mc, 'goods_$sorting')) {  
        $data =array();
        $data['total'] = count($goods);
        $data['goods'] = array();

        //reverse array for order by desc
        if ($order == 'desc') {
          $goods = array_reverse($goods);
        }

        //select goods from range
        foreach ($goods as $index => $good) {
          if ($index + 1 > $offset && $index < $offset + $limit) {
            //load good info
            $data['goods'][] = this->GET_GOOD($good['id']);
          }
        }
      }
      else {
        //load data from db
        $sql = "SELECT id, price FROM goods ORDER BY $sorting ASC";

        $searchGoods = $mysqli->query($sql, MYSQLI_STORE_RESULT);
        if ($mysqli->errno) {
          die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
          header('Requets error', true, 400);
          echo json_encode(array("message"=>"Goods not found"));
        }
        else{   
          $data = array();
          $data['goods'] = array();

          $mcGoods = array();
          while ($row = $searchGoods->fetch_assoc()) {
            $mcGoods[] = $row;
          }

          //set data to memcached
          memcache_set($mc, 'goods_$sorting', $mcGoods, MEMCACHE_COMPRESSED, 60*60);

          //reverse array for order by desc
          if ($order == 'desc') {
            $mcGoods = array_reverse($mcGoods);
          }

          //select goods from range
          foreach ($mcGoods as $index => $good) {
            if ($index + 1 > $offset && $index < $offset + $limit) {
              //load good info
              $data['goods'][] = this->GET_GOOD($good['id']);
            }
          }

          $data['total'] = count($mcGoods);
        }

        $data['offset'] = $offset;
        $data['limit'] = $limit;
        $data['sorting'] = $sorting;
        $data['order'] = $order;

        echo json_encode($data);
      }
    }

    //create good
    public function POST($arg = false) {
      global $mysqli;

      $postdata = file_get_contents("php://input");
      $request = json_decode($postdata);

      //check params
      if (!isset($request->title) OR 
        !isset($request->description) OR 
        !isset($request->price) OR
        !isset($request->img) 
      ) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid data"));
        return;
      }

      //parse params
      $title = $mysqli->real_escape_string($request->title);
      $description = $mysqli->real_escape_string($request->description);
      $price = $mysqli->real_escape_string($request->price);
      $imgData = $mysqli->real_escape_string($request->img);

      //check price
      if (!is_numeric($price)) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid price"));
        return;
      }

      $imgUrl = image::save($imgData);

      //save good
      $sql = "INSERT INTO goods (title, description, price, img) VALUES ('$title', '$description', '$price', '$imgUrl')";

      $addGood = $mysqli->query($sql, MYSQLI_STORE_RESULT);
      if ($mysqli->errno) {
        die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Goods not saved"));
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
        memcache_set($mc, 'good_id_$id', $good, MEMCACHE_COMPRESSED, 60*60);
        this->ADD_GOOD_TO_MC('id', $good);
        this->ADD_GOOD_TO_MC('price', $good);

        echo json_encode($good);
      }
    }

    //update good
    public function PUT($id) {
      global $mysqli;

      $postdata = file_get_contents("php://input");
      $request = json_decode($postdata);

      //check params
      if (!isset($id) OR 
        !isset($request->title) OR 
        !isset($request->description) OR 
        !isset($request->price) OR
        !isset($request->img) 
      ) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid data"));
        return;
      }

      //parse params
      $title = $mysqli->real_escape_string($request->title);
      $description = $mysqli->real_escape_string($request->description);
      $price = $mysqli->real_escape_string($request->price);
      $imgData = $mysqli->real_escape_string($request->img);

      //check price
      if (!is_numeric($price)) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid price");
        return;
      }

      $imgUrl = image::save($imgData);

      //update good
      $sql = "UPDATE goods SET title='$title', description='$description', price='$price', img='$imgUrl' WHERE id=$id";

      $updateGood = $mysqli->query($sql, MYSQLI_STORE_RESULT);
      if ($mysqli->errno) {
        die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Goods not updated");
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
        memcache_set($mc, 'good_id_$id', $good, MEMCACHE_COMPRESSED, 60*60);
        this->UPDATE_GOOOD_IN_MC($good);

        echo json_encode($good);
      }
    }

    //delete good
    public function DELETE($id = false) {
      global $mysqli;

      //check params
      if (!isset($id)) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid data"));
        return;
      }

      //delete good
      $sql = "DELETE FROM goods WHERE id=$id";

      $deleteGood = $mysqli->query($sql, MYSQLI_STORE_RESULT);
      if ($mysqli->errno) {
        die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Goods not deleted"));
      }
      else{
        //delete good from memcached
        memcache_delete($mc, 'good_id_$id')
        this->DELETE_GOOD_FROM_MC('id', $id);
        this->DELETE_GOOD_FROM_MC('price', $id);

        echo json_encode(array("message"=>"Good $id was deleted"));
      }
    }

    //load good info
    private function GET_GOOD($id){
      if ($good = memcache_get($mc, 'good_id_$id')){
        return $good;
      }
      else{
        $sql = "SELECT * FROM goods WHERE id=$id";

        $searchGood = $mysqli->query($sql, MYSQLI_STORE_RESULT);
        if ($mysqli->errno) {
          die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
          header('Requets error', true, 400);
          echo json_encode(array("message"=>"Good not found"));
        }
        else{  
          $good = $searchGood->fetch_array();
          memcache_set($mc, 'good_id_$id', $good, MEMCACHE_COMPRESSED, 60*60);
          return $good;
        } 
      }
    }

    //delete good id from sorted list in memcached
    private function DELETE_GOOD_FROM_MC($sorting, $id){
      if ($goods = memcache_get($mc, 'goods_$sorting')){
        $newList = array();
        foreach ($goods as $good) {
          if ($id != $good['id']) {
            $newList[] = $good['id'];
          }
        }
        memcache_set($mc, 'goods_$sorting', $newList, MEMCACHE_COMPRESSED, 60*60);
      }
      return false;
    }

    //add good id to sorted list in memcached
    private function ADD_GOOD_TO_MC($sorting, $newGood){
      if ($goods = memcache_get($mc, 'goods_$sorting')){
        $newList = array();
        $countGoods = count($goods);
        foreach ($goods as $index => $good) {
          if ($index == 0 && $newGood[$sorting] < $good[$sorting]){
            $newList[] = $newGood;
            $newList[] = $good;
          }
          else if ($index + 1 == countGoods && $newGood[$sorting] >= $good[$sorting]) {
            $newList[] = $good;
            $newList[] = $newGood;
          }
          else if ($goods[$index - 1][$sorting] < $newGood[$sorting] && $newGood[$sorting] <= $goods[$index + 1][$sorting]) {
            $newList[] = $good;
            $newList[] = $newGood;
          }
          else {
            $newList[] = $good;
          }
        }
        memcache_set($mc, 'goods_$sorting', $newList, MEMCACHE_COMPRESSED, 60*60);
      }
      return false;
    }

    //update sorted list by price in memcached
    private function UPDATE_GOOOD_IN_MC($newGood){
      $id = $newGood['id'];
      if ($oldGood = memcache_get($mc, 'good_id_$id' && $oldGood['price'] != $newGood['price'] && $goods = memcache_get($mc, 'goods_price'){
          this->DELETE_GOOD_FROM_MC('price', $id)
          this->ADD_GOOD_TO_MC('price', $newGood)
        }
      }
      return false;
    }
  }
?>