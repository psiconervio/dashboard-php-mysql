<?php
  require 'database.php';
  
  //---------------------------------------- Condition to check that POST value is not empty.
  if (!empty($_POST)) {
    //........................................ keep track POST values
    $id = $_POST['id'];
    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];
    $status_read_sensor_dht11 = $_POST['status_read_sensor_dht11'];
    //........................................
    
    //........................................ Updating the data in the table.
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "UPDATE esp32_table_test SET temperature = ?, humidity = ?, status_read_sensor_dht11 = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($temperature,$humidity,$status_read_sensor_dht11,$id));
    Database::disconnect();
    //........................................ 
  }
  //---------------------------------------- 
?>