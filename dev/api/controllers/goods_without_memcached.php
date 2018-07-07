<?php
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

      $limit = $mysqli->real_escape_string($_GET['limit']);
      $sorting = $mysqli->real_escape_string($_GET['sorting']);
      $offset = $mysqli->real_escape_string($_GET['offset']); 
      $order = $mysqli->real_escape_string($_GET['order']); 

      $sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM goods ORDER BY $sorting $order LIMIT $offset, $limit";

      $searchGoods = $mysqli->query($sql, MYSQLI_STORE_RESULT);
      if ($mysqli->errno) {
        die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Goods not found"));
      }
      else{   
        $data =array();
        $data['goods'] = array();

        $countQuery= $mysqli->query("SELECT FOUND_ROWS()", MYSQLI_STORE_RESULT);
        $count = $countQuery->fetch_array();
        $data['total'] = $count['FOUND_ROWS()'];

        while ($row = $searchGoods->fetch_assoc()) {
          $row['id'] = intval($row['id']);
          $row['price'] = floatval($row['price']);
          $data['goods'][] = $row;
        }
        
        $data['offset'] = $offset;
        $data['limit'] = $limit;
        $data['sorting'] = $sorting;
        $data['order'] = $order;
        
        echo json_encode($data);
      }
    }

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

      $title = $mysqli->real_escape_string($request->title);
      $description = $mysqli->real_escape_string($request->description);
      $price = $mysqli->real_escape_string($request->price);
      $imgData = $mysqli->real_escape_string($request->img);

      if (!is_numeric($price)) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid price"));
        return;
      }

      $imgUrl = image::save($imgData);

      $sql = "INSERT INTO goods (title, description, price, img) VALUES ('$title', '$description', '$price', '$imgUrl')";

      $addGood = $mysqli->query($sql, MYSQLI_STORE_RESULT);
      if ($mysqli->errno) {
        die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Goods not saved"));
      }
      else{ 
        $good = array(  
          "id" => intval($mysqli->insert_id),
          "title" => $title,
          "price" => floatval($price),
          "description" => $description,
          "img" => $imgUrl
        );
        echo json_encode($good);
      }
    }

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

      $title = $mysqli->real_escape_string($request->title);
      $description = $mysqli->real_escape_string($request->description);
      $price = $mysqli->real_escape_string($request->price);
      $imgData = $mysqli->real_escape_string($request->img);

      if (!is_numeric($price)) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid price"));
        return;
      }

      $imgUrl = image::save($imgData);

      $sql = "UPDATE goods SET title='$title', description='$description', price='$price', img='$imgUrl' WHERE id=$id";

      $updateGood = $mysqli->query($sql, MYSQLI_STORE_RESULT);
      if ($mysqli->errno) {
        die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Goods not updated"));
      }
      else{ 
        $good = array(  
          "id" => intval($id),
          "title" => $title,
          "price" => floatval($price),
          "description" => $description,
          "img" => $imgUrl
        );
        echo json_encode($good);
      }
    }

    public function DELETE($id = false) {
      global $mysqli;

      //check params
      if (!isset($id)) {
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Request has not valid data"));
        return;
      }

      $sql = "DELETE FROM goods WHERE id=$id";

      $deleteGood = $mysqli->query($sql, MYSQLI_STORE_RESULT);
      if ($mysqli->errno) {
        die('Select Error (' . $mysqli->errno . ') ' . $mysqli->error);
        header('Requets error', true, 400);
        echo json_encode(array("message"=>"Goods not deleted"));
      }
      else{
        echo json_encode(array("message"=>"Good $id was deleted"));
      }
    }
  }
?>