<?php
  function error($code, $message, $log) {
    header("Error", true, $code);
    echo json_encode(array("message"=>$message));

    if (isset($log)) {
      error_log( date("Y-m-d H:i:s") ." ERROR MESSAGE: ". json_encode($log) ."\n", 3, "./logs/errorLog.log");
    }
  }
?>