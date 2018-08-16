<?php
 function imageSave($imgData) {
    //check data
    if (preg_match("/^\/content\//i", $imgData)) {
      return $imgData;
    }
    if (!preg_match("/^data:image/i", $imgData)) {
      return "";
    }
    
    //create new title
    $now = new DateTime();
    $imgName = "good_";
    $imgName .= $now->format("U");
    $imgName .= rand();
    $imgName = md5($imgName);
    $imgName .= ".jpg";

    //base64 decode data
    $imgData = base64_decode(preg_replace("#^data:image/\w+;base64,#i", "", $imgData));

    //create new file
    $file = "../content/goods/";
    $file .= $imgName;
    file_put_contents($file, $imgData);
    $imgUrl = "/content/goods/$imgName";

    //get width and height of image
    list($width, $height) = getimagesize($file);

    //define new width and height
    if ($width >= $height){
      $newWidth = 188;
      $newHeight = $height*(188/$width);
    }
    else{
      $newWidth = $width*(188/$height);
      $newHeight = 188;
    }

    //resize image
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    $source = imagecreatefromjpeg($file);
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagejpeg($thumb, $file);

    return $imgUrl;
  }
?>