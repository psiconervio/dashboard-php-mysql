<!-- // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> updateDHT11data_and_recordtable.php
// PHP code to update and record DHT11 sensor data and LEDs state in table. -->
<?php
  require 'database.php';
  
  // Function to handle POST data and update/insert records.
  function handlePostData($tablePrefix) {
    if (!empty($_POST)) {
      // Keep track of POST values
      $id = $_POST['id'];
      $temperature = $_POST['temperature'];
      $humidity = $_POST['humidity'];
      $status_read_sensor_dht11 = $_POST['status_read_sensor_dht11'];
      $led_01 = $_POST['led_01'];
      $led_02 = $_POST['led_02'];
      
      // Get the time and date.
      date_default_timezone_set("America/Argentina/Catamarca");
      $tm = date("H:i:s");
      $dt = date("Y-m-d");
      
      // Updating the data in the table.
      $pdo = Database::connect();
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "UPDATE ${tablePrefix}_dht11_leds_update SET temperature = ?, humidity = ?, status_read_sensor_dht11 = ?, time = ?, date = ? WHERE id = ?";
      $q = $pdo->prepare($sql);
      $q->execute(array($temperature, $humidity, $status_read_sensor_dht11, $tm, $dt, $id));
      Database::disconnect();
      
      // Entering data into a new row in the table.
      $id_key;
      $board = $_POST['id'];
      $found_empty = false;
      
      $pdo = Database::connect();
      
      // Process to check if "id" is already in use.
      while (!$found_empty) {
        $id_key = generate_string_id(10);
        $sql = "SELECT * FROM ${tablePrefix}_dht11_leds_update WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id_key));
        
        if (!$data = $q->fetch()) {
          $found_empty = true;
        }
      }
      
      // The process of entering data into a new row.
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "INSERT INTO ${tablePrefix}_dht11_leds_update (id, board, temperature, humidity, status_read_sensor_dht11, LED_01, LED_02, time, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $q = $pdo->prepare($sql);
      $q->execute(array($id_key, $board, $temperature, $humidity, $status_read_sensor_dht11, $led_01, $led_02, $tm, $dt));
      
      Database::disconnect();
    }
  }
  
  // Function to create "id" based on numbers and characters.
  function generate_string_id($strength = 16) {
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($permitted_chars);
    $random_string = '';
    for ($i = 0; $i < $strength; $i++) {
      $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
      $random_string .= $random_character;
    }
    return $random_string;
  }
  
  // Handle POST data for the first ESP32.
  handlePostData('esp32_01');
  
  // Handle POST data for the second ESP32 (add more as needed).
  handlePostData('esp32_02');
?>
