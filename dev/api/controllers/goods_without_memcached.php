<?php
  class goods {
    public function GET($urlData, $formData) {
      global $mysqli;
      
      //check params
      if (
        !isset($formData["limit"]) OR 
        !isset($formData["offset"]) OR 
        !isset($formData["sorting"]) OR 
        !isset($formData["order"])
      ) {
        error(400, "Request has not all params", $formData);
        return;
      }

      $formData["limit"] = intval($formData["limit"]);
      $formData["offset"] = intval($formData["offset"]);

      if (
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

      $sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM goods ORDER BY $sorting $order LIMIT $offset, $limit";

      $searchGoods = $mysqli->query($sql);
      if ($mysqli->errno) {
        error(400, "Goods not found", "Select Error (" . $mysqli->errno . ") " . $mysqli->error);
      }
      else{   
        $data =array();
        $data["goods"] = array();

        $countQuery= $mysqli->query("SELECT FOUND_ROWS()");
        $count = $countQuery->fetch_array();
        $data["total"] = $count["FOUND_ROWS()"];

        while ($row = $searchGoods->fetch_assoc()) {
          $row["id"] = intval($row["id"]);
          $row["price"] = floatval($row["price"]);
          $data["goods"][] = $row;
        }
        
        $data["offset"] = $offset;
        $data["limit"] = $limit;
        $data["sorting"] = $sorting;
        $data["order"] = $order;
        
        echo json_encode($data);
      }
    }

    public function POST($urlData, $formData) {
      global $mysqli;

      //check params
      if (
        !isset($formData["title"]) OR 
        !isset($formData["description"]) OR 
        !isset($formData["price"]) OR
        !isset($formData["img"]) OR

        !is_numeric($formData["price"]) OR
        $formData["price"] <= 0 OR
        $formData["price"] > 1000000 OR

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

    public function PUT($urlData, $formData) {
      global $mysqli;

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
        $formData["price"] > 1000000 OR

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
        echo json_encode($good);
      }
    }

    public function DELETE($urlData, $formData) {
      global $mysqli;

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

      $sql = "DELETE FROM goods WHERE id=$id";

      $deleteGood = $mysqli->query($sql);
      if ($mysqli->errno) {
        error(400, "Goods not deleted", "Select Error (" . $mysqli->errno . ") " . $mysqli->error);
      }
      else{
        echo json_encode(array("message"=>"Good $id was deleted"));
      }
    }
  }
?>