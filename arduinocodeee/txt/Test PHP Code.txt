// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> database.php
// PHP code to access the database.
<?php
	class Database {
		private static $dbName = 'REPLACE_WITH_YOUR_DATABASE_NAME'; // Example: private static $dbName = 'myDB';
		private static $dbHost = 'REPLACE_WITH_YOUR_HOST_NAME_OR_SERVER_NAME'; // Example: private static $dbHost = 'localhost';
		private static $dbUsername = 'REPLACE_WITH_YOUR_USERNAME'; // Example: private static $dbUsername = 'myUserName';
		private static $dbUserPassword = 'REPLACE_WITH_YOUR_PASSWORD'; // // Example: private static $dbUserPassword = 'myPassword';
		 
		private static $cont  = null;
		 
		public function __construct() {
			die('Init function is not allowed');
		}
		 
		public static function connect() {
      // One connection through whole application
      if ( null == self::$cont ) {     
        try {
          self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword); 
        } catch(PDOException $e) {
          die($e->getMessage()); 
        }
      }
      return self::$cont;
		}
		 
		public static function disconnect() {
			self::$cont = null;
		}
	}
?>
// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<



############################################################################################
############################################################################################
############################################################################################
############################################################################################



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> updateDHT11data.php
// PHP code to update DHT11 sensor data in the table/database.
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
    $sql = "UPDATE replace_with_your_table_name SET temperature = ?, humidity = ?, status_read_sensor_dht11 = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($temperature,$humidity,$status_read_sensor_dht11,$id));
    Database::disconnect();
    //........................................ 
  }
  //---------------------------------------- 
?>
// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<



############################################################################################
############################################################################################
############################################################################################
############################################################################################



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> getdata.php
// PHP file to get LEDs state stored in the table/database.
<?php
  include 'database.php';
  
  //---------------------------------------- Condition to check that POST value is not empty.
  if (!empty($_POST)) {
    // keep track post values
    $id = $_POST['id'];
    
    $myObj = (object)array();
    
    //........................................ 
    $pdo = Database::connect();
    $sql = 'SELECT * FROM replace_with_your_table_name WHERE id="' . $id . '"';
    foreach ($pdo->query($sql) as $row) {
      $myObj->id = $row['id'];
      $myObj->LED_01 = $row['LED_01'];
      $myObj->LED_02 = $row['LED_02'];
      
      $myJSON = json_encode($myObj);
      
      echo $myJSON;
    }
    Database::disconnect();
    //........................................ 
  }
  //---------------------------------------- 
?>
// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<