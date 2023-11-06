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



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> updateDHT11data_and_recordtable.php
// PHP code to update and record DHT11 sensor data and LEDs state in table.
<?php
  require 'database.php';
  
  //---------------------------------------- Condition to check that POST value is not empty.
  if (!empty($_POST)) {
    //........................................ keep track POST values
    $id = $_POST['id'];
    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];
    $status_read_sensor_dht11 = $_POST['status_read_sensor_dht11'];
    $led_01 = $_POST['led_01'];
    $led_02 = $_POST['led_02'];
    //........................................
    
    //........................................ Get the time and date.
    date_default_timezone_set("Asia/Jakarta"); // Look here for your timezone : https://www.php.net/manual/en/timezones.php
    $tm = date("H:i:s");
    $dt = date("Y-m-d");
    //........................................
    
    //........................................ Updating the data in the table.
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // replace_with_your_table_name, on this project I use the table name 'esp32_table_dht11_leds_update'.
    // This table is used to store DHT11 sensor data updated by ESP32. 
    // This table is also used to store the state of the LEDs, the state of the LEDs is controlled from the "home.php" page. 
    // This table is operated with the "UPDATE" command, so this table will only contain one row.
    $sql = "UPDATE replace_with_your_table_name SET temperature = ?, humidity = ?, status_read_sensor_dht11 = ?, time = ?, date = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($temperature,$humidity,$status_read_sensor_dht11,$tm,$dt,$id));
    Database::disconnect();
    //........................................ 
    
    //........................................ Entering data into a table.
    $id_key;
    $board = $_POST['id'];
    $found_empty = false;
    
    $pdo = Database::connect();
    
    //:::::::: Process to check if "id" is already in use.
    while ($found_empty == false) {
      $id_key = generate_string_id(10);
      // replace_with_your_table_name, on this project I use the table name 'esp32_table_dht11_leds_record'.
      // This table is used to store and record DHT11 sensor data updated by ESP32. 
      // This table is also used to store and record the state of the LEDs, the state of the LEDs is controlled from the "home.php" page. 
      // This table is operated with the "INSERT" command, so this table will contain many rows.
      // Before saving and recording data in this table, the "id" will be checked first, to ensure that the "id" that has been created has not been used in the table.
      $sql = 'SELECT * FROM replace_with_your_table_name WHERE id="' . $id_key . '"';
      $q = $pdo->prepare($sql);
      $q->execute();
      
      if (!$data = $q->fetch()) {
        $found_empty = true;
      }
    }
    //::::::::
    
    //:::::::: The process of entering data into a table.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // replace_with_your_table_name, on this project I use the table name 'esp32_table_dht11_leds_record'.
    // This table is used to store and record DHT11 sensor data updated by ESP32. 
    // This table is also used to store and record the state of the LEDs, the state of the LEDs is controlled from the "home.php" page. 
    // This table is operated with the "INSERT" command, so this table will contain many rows.
		$sql = "INSERT INTO replace_with_your_table_name (id,board,temperature,humidity,status_read_sensor_dht11,LED_01,LED_02,time,date) values(?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$q = $pdo->prepare($sql);
		$q->execute(array($id_key,$board,$temperature,$humidity,$status_read_sensor_dht11,$led_01,$led_02,$tm,$dt));
    //::::::::
    
    Database::disconnect();
    //........................................ 
  }
  //---------------------------------------- 
  
  //---------------------------------------- Function to create "id" based on numbers and characters.
  function generate_string_id($strength = 16) {
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($permitted_chars);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
      $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
      $random_string .= $random_character;
    }
    return $random_string;
  }
  //---------------------------------------- 
?>
// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<



############################################################################################
############################################################################################
############################################################################################
############################################################################################



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> getdata.php
// PHP file to get DHT11 sensor data and LEDs state stored in table in database to display on "home.php" page.
// This file is also accessed by ESP32 to get the state of the LEDs, the state of the LEDs is controlled from the "home.php" page.
<?php
  include 'database.php';
  
  //---------------------------------------- Condition to check that POST value is not empty.
  if (!empty($_POST)) {
    // keep track post values
    $id = $_POST['id'];
    
    $myObj = (object)array();
    
    //........................................ 
    $pdo = Database::connect();
    // replace_with_your_table_name, on this project I use the table name 'esp32_table_dht11_leds_update'.
    // This table is used to store DHT11 sensor data updated by ESP32. 
    // This table is also used to store the state of the LEDs, the state of the LEDs is controlled from the "home.php" page. 
    // To store data, this table is operated with the "UPDATE" command, so this table contains only one row.
    $sql = 'SELECT * FROM replace_with_your_table_name WHERE id="' . $id . '"';
    foreach ($pdo->query($sql) as $row) {
      $date = date_create($row['date']);
      $dateFormat = date_format($date,"d-m-Y");
      
      $myObj->id = $row['id'];
      $myObj->temperature = $row['temperature'];
      $myObj->humidity = $row['humidity'];
      $myObj->status_read_sensor_dht11 = $row['status_read_sensor_dht11'];
      $myObj->LED_01 = $row['LED_01'];
      $myObj->LED_02 = $row['LED_02'];
      $myObj->ls_time = $row['time'];
      $myObj->ls_date = $dateFormat;
      
      $myJSON = json_encode($myObj);
      
      echo $myJSON;
    }
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



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> updateLEDs.php
// PHP code to update the state of the LEDs, the state of the LEDs is controlled from the "home.php" page.
<?php
  require 'database.php';
  
  //---------------------------------------- Condition to check that POST value is not empty.
  if (!empty($_POST)) {
    //........................................ keep track post values
    $id = $_POST['id'];
    $lednum = $_POST['lednum'];
    $ledstate = $_POST['ledstate'];
    //........................................ 
    
    //........................................ 
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // replace_with_your_table_name, on this project I use the table name 'esp32_table_dht11_leds_update'.
    // This table is used to store DHT11 sensor data updated by ESP32. 
    // This table is also used to store the state of the LEDs, the state of the LEDs is controlled from the "home.php" page. 
    // To store data, this table is operated with the "UPDATE" command, so this table contains only one row.
    $sql = "UPDATE replace_with_your_table_name SET " . $lednum . " = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($ledstate,$id));
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



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> home.php
// PHP/HTML code to display DHT11 sensor data and control LEDs state.
<!DOCTYPE HTML>
<html>
  <head>
    <title>ESP32 WITH MYSQL DATABASE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="icon" href="data:,">
    <style>
      html {font-family: Arial; display: inline-block; text-align: center;}
      p {font-size: 1.2rem;}
      h4 {font-size: 0.8rem;}
      body {margin: 0;}
      .topnav {overflow: hidden; background-color: #0c6980; color: white; font-size: 1.2rem;}
      .content {padding: 5px; }
      .card {background-color: white; box-shadow: 0px 0px 10px 1px rgba(140,140,140,.5); border: 1px solid #0c6980; border-radius: 15px;}
      .card.header {background-color: #0c6980; color: white; border-bottom-right-radius: 0px; border-bottom-left-radius: 0px; border-top-right-radius: 12px; border-top-left-radius: 12px;}
      .cards {max-width: 700px; margin: 0 auto; display: grid; grid-gap: 2rem; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));}
      .reading {font-size: 1.3rem;}
      .packet {color: #bebebe;}
      .temperatureColor {color: #fd7e14;}
      .humidityColor {color: #1b78e2;}
      .statusreadColor {color: #702963; font-size:12px;}
      .LEDColor {color: #183153;}
      
      /* ----------------------------------- Toggle Switch */
      .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
      }

      .switch input {display:none;}

      .sliderTS {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #D3D3D3;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 34px;
      }

      .sliderTS:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: #f7f7f7;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 50%;
      }

      input:checked + .sliderTS {
        background-color: #00878F;
      }

      input:focus + .sliderTS {
        box-shadow: 0 0 1px #2196F3;
      }

      input:checked + .sliderTS:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
      }

      .sliderTS:after {
        content:'OFF';
        color: white;
        display: block;
        position: absolute;
        transform: translate(-50%,-50%);
        top: 50%;
        left: 70%;
        font-size: 10px;
        font-family: Verdana, sans-serif;
      }

      input:checked + .sliderTS:after {  
        left: 25%;
        content:'ON';
      }

      input:disabled + .sliderTS {  
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
      }
      /* ----------------------------------- */
    </style>
  </head>
  
  <body>
    <div class="topnav">
      <h3>ESP32 WITH MYSQL DATABASE</h3>
    </div>
    
    <br>
    
    <!-- __ DISPLAYS MONITORING AND CONTROLLING ____________________________________________________________________________________________ -->
    <div class="content">
      <div class="cards">
        
        <!-- == MONITORING ======================================================================================== -->
        <div class="card">
          <div class="card header">
            <h3 style="font-size: 1rem;">MONITORING</h3>
          </div>
          
          <!-- Displays the humidity and temperature values received from ESP32. *** -->
          <h4 class="temperatureColor"><i class="fas fa-thermometer-half"></i> TEMPERATURE</h4>
          <p class="temperatureColor"><span class="reading"><span id="ESP32_01_Temp"></span> &deg;C</span></p>
          <h4 class="humidityColor"><i class="fas fa-tint"></i> HUMIDITY</h4>
          <p class="humidityColor"><span class="reading"><span id="ESP32_01_Humd"></span> &percnt;</span></p>
          <!-- *********************************************************************** -->
          
          <p class="statusreadColor"><span>Status Read Sensor DHT11 : </span><span id="ESP32_01_Status_Read_DHT11"></span></p>
        </div>
        <!-- ======================================================================================================= -->
        
        <!-- == CONTROLLING ======================================================================================== -->
        <div class="card">
          <div class="card header">
            <h3 style="font-size: 1rem;">CONTROLLING</h3>
          </div>
          
          <!-- Buttons for controlling the LEDs on Slave 2. ************************** -->
          <h4 class="LEDColor"><i class="fas fa-lightbulb"></i> LED 1</h4>
          <label class="switch">
            <input type="checkbox" id="ESP32_01_TogLED_01" onclick="GetTogBtnLEDState('ESP32_01_TogLED_01')">
            <div class="sliderTS"></div>
          </label>
          <h4 class="LEDColor"><i class="fas fa-lightbulb"></i> LED 2</h4>
          <label class="switch">
            <input type="checkbox" id="ESP32_01_TogLED_02" onclick="GetTogBtnLEDState('ESP32_01_TogLED_02')">
            <div class="sliderTS"></div>
          </label>
          <!-- *********************************************************************** -->
        </div>  
        <!-- ======================================================================================================= -->
        
      </div>
    </div>
    
    <br>
    
    <div class="content">
      <div class="cards">
        <div class="card header" style="border-radius: 15px;">
            <h3 style="font-size: 0.7rem;">LAST TIME RECEIVED DATA FROM ESP32 [ <span id="ESP32_01_LTRD"></span> ]</h3>
            <button onclick="window.open('recordtable.php', '_blank');">Open Record Table</button>
            <h3 style="font-size: 0.7rem;"></h3>
        </div>
      </div>
    </div>
    <!-- ___________________________________________________________________________________________________________________________________ -->
    
    <script>
      //------------------------------------------------------------
      document.getElementById("ESP32_01_Temp").innerHTML = "NN"; 
      document.getElementById("ESP32_01_Humd").innerHTML = "NN";
      document.getElementById("ESP32_01_Status_Read_DHT11").innerHTML = "NN";
      document.getElementById("ESP32_01_LTRD").innerHTML = "NN";
      //------------------------------------------------------------
      
      Get_Data("esp32_01");
      
      setInterval(myTimer, 5000);
      
      //------------------------------------------------------------
      function myTimer() {
        Get_Data("esp32_01");
      }
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function Get_Data(id) {
				if (window.XMLHttpRequest) {
          // code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp = new XMLHttpRequest();
        } else {
          // code for IE6, IE5
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            const myObj = JSON.parse(this.responseText);
            if (myObj.id == "esp32_01") {
              document.getElementById("ESP32_01_Temp").innerHTML = myObj.temperature;
              document.getElementById("ESP32_01_Humd").innerHTML = myObj.humidity;
              document.getElementById("ESP32_01_Status_Read_DHT11").innerHTML = myObj.status_read_sensor_dht11;
              document.getElementById("ESP32_01_LTRD").innerHTML = "Time : " + myObj.ls_time + " | Date : " + myObj.ls_date + " (dd-mm-yyyy)";
              if (myObj.LED_01 == "ON") {
                document.getElementById("ESP32_01_TogLED_01").checked = true;
              } else if (myObj.LED_01 == "OFF") {
                document.getElementById("ESP32_01_TogLED_01").checked = false;
              }
              if (myObj.LED_02 == "ON") {
                document.getElementById("ESP32_01_TogLED_02").checked = true;
              } else if (myObj.LED_02 == "OFF") {
                document.getElementById("ESP32_01_TogLED_02").checked = false;
              }
            }
          }
        };
        xmlhttp.open("POST","getdata.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("id="+id);
			}
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function GetTogBtnLEDState(togbtnid) {
        if (togbtnid == "ESP32_01_TogLED_01") {
          var togbtnchecked = document.getElementById(togbtnid).checked;
          var togbtncheckedsend = "";
          if (togbtnchecked == true) togbtncheckedsend = "ON";
          if (togbtnchecked == false) togbtncheckedsend = "OFF";
          Update_LEDs("esp32_01","LED_01",togbtncheckedsend);
        }
        if (togbtnid == "ESP32_01_TogLED_02") {
          var togbtnchecked = document.getElementById(togbtnid).checked;
          var togbtncheckedsend = "";
          if (togbtnchecked == true) togbtncheckedsend = "ON";
          if (togbtnchecked == false) togbtncheckedsend = "OFF";
          Update_LEDs("esp32_01","LED_02",togbtncheckedsend);
        }
      }
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function Update_LEDs(id,lednum,ledstate) {
				if (window.XMLHttpRequest) {
          // code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp = new XMLHttpRequest();
        } else {
          // code for IE6, IE5
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            //document.getElementById("demo").innerHTML = this.responseText;
          }
        }
        xmlhttp.open("POST","updateLEDs.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("id="+id+"&lednum="+lednum+"&ledstate="+ledstate);
			}
      //------------------------------------------------------------
    </script>
  </body>
</html>
// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<



############################################################################################
############################################################################################
############################################################################################
############################################################################################



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> recordtable.php
// PHP/HTML code to display a record table of DHT11 sensor data and LEDs state.
<!DOCTYPE HTML>
<html>
  <head>
    <title>ESP32 WITH MYSQL DATABASE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      html {font-family: Arial; display: inline-block; text-align: center;}
      p {font-size: 1.2rem;}
      h4 {font-size: 0.8rem;}
      body {margin: 0;}
      /* ----------------------------------- TOPNAV STYLE */
      .topnav {overflow: hidden; background-color: #0c6980; color: white; font-size: 1.2rem;}
      /* ----------------------------------- */
      
      /* ----------------------------------- TABLE STYLE */
      .styled-table {
        border-collapse: collapse;
        margin-left: auto; 
        margin-right: auto;
        font-size: 0.9em;
        font-family: sans-serif;
        min-width: 400px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        border-radius: 0.5em;
        overflow: hidden;
        width: 90%;
      }

      .styled-table thead tr {
        background-color: #0c6980;
        color: #ffffff;
        text-align: left;
      }

      .styled-table th {
        padding: 12px 15px;
        text-align: left;
      }

      .styled-table td {
        padding: 12px 15px;
        text-align: left;
      }

      .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
      }

      .styled-table tbody tr.active-row {
        font-weight: bold;
        color: #009879;
      }

      .bdr {
        border-right: 1px solid #e3e3e3;
        border-left: 1px solid #e3e3e3;
      }
      
      td:hover {background-color: rgba(12, 105, 128, 0.21);}
      tr:hover {background-color: rgba(12, 105, 128, 0.15);}
      .styled-table tbody tr:nth-of-type(even):hover {background-color: rgba(12, 105, 128, 0.15);}
      /* ----------------------------------- */
      
      /* ----------------------------------- BUTTON STYLE */
      .btn-group .button {
        background-color: #0c6980; /* Green */
        border: 1px solid #e3e3e3;
        color: white;
        padding: 5px 8px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        cursor: pointer;
        float: center;
      }

      .btn-group .button:not(:last-child) {
        border-right: none; /* Prevent double borders */
      }

      .btn-group .button:hover {
        background-color: #094c5d;
      }

      .btn-group .button:active {
        background-color: #0c6980;
        transform: translateY(1px);
      }

      .btn-group .button:disabled,
      .button.disabled{
        color:#fff;
        background-color: #a0a0a0; 
        cursor: not-allowed;
        pointer-events:none;
      }
      /* ----------------------------------- */
    </style>
  </head>
  
  <body>
    <div class="topnav">
      <h3>ESP32 WITH MYSQL DATABASE</h3>
    </div>
    
    <br>
    
    <h3 style="color: #0c6980;">ESP32_01 RECORD DATA TABLE</h3>
    
    <table class="styled-table" id= "table_id">
      <thead>
        <tr>
          <th>NO</th>
          <th>ID</th>
          <th>BOARD</th>
          <th>TEMPERATURE (°C)</th>
          <th>HUMIDITY (%)</th>
          <th>STATUS READ SENSOR DHT11</th>
          <th>LED 01</th>
          <th>LED 02</th>
          <th>TIME</th>
          <th>DATE (dd-mm-yyyy)</th>
        </tr>
      </thead>
      <tbody id="tbody_table_record">
        <?php
          include 'database.php';
          $num = 0;
          //------------------------------------------------------------ The process for displaying a record table containing the DHT11 sensor data and the state of the LEDs.
          $pdo = Database::connect();
          // replace_with_your_table_name, on this project I use the table name 'esp32_table_dht11_leds_record'.
          // This table is used to store and record DHT11 sensor data updated by ESP32. 
          // This table is also used to store and record the state of the LEDs, the state of the LEDs is controlled from the "home.php" page. 
          // To store data, this table is operated with the "INSERT" command, so this table will contain many rows.
          $sql = 'SELECT * FROM replace_with_your_table_name ORDER BY date, time';
          foreach ($pdo->query($sql) as $row) {
            $date = date_create($row['date']);
            $dateFormat = date_format($date,"d-m-Y");
            $num++;
            echo '<tr>';
            echo '<td>'. $num . '</td>';
            echo '<td class="bdr">'. $row['id'] . '</td>';
            echo '<td class="bdr">'. $row['board'] . '</td>';
            echo '<td class="bdr">'. $row['temperature'] . '</td>';
            echo '<td class="bdr">'. $row['humidity'] . '</td>';
            echo '<td class="bdr">'. $row['status_read_sensor_dht11'] . '</td>';
            echo '<td class="bdr">'. $row['LED_01'] . '</td>';
            echo '<td class="bdr">'. $row['LED_02'] . '</td>';
            echo '<td class="bdr">'. $row['time'] . '</td>';
            echo '<td>'. $dateFormat . '</td>';
            echo '</tr>';
          }
          Database::disconnect();
          //------------------------------------------------------------
        ?>
      </tbody>
    </table>
    
    <br>
    
    <div class="btn-group">
      <button class="button" id="btn_prev" onclick="prevPage()">Prev</button>
      <button class="button" id="btn_next" onclick="nextPage()">Next</button>
      <div style="display: inline-block; position:relative; border: 0px solid #e3e3e3; float: center; margin-left: 2px;;">
        <p style="position:relative; font-size: 14px;"> Table : <span id="page"></span></p>
      </div>
      <select name="number_of_rows" id="number_of_rows">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
      <button class="button" id="btn_apply" onclick="apply_Number_of_Rows()">Apply</button>
    </div>

    <br>
    
    <script>
      //------------------------------------------------------------
      var current_page = 1;
      var records_per_page = 10;
      var l = document.getElementById("table_id").rows.length
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function apply_Number_of_Rows() {
        var x = document.getElementById("number_of_rows").value;
        records_per_page = x;
        changePage(current_page);
      }
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function prevPage() {
        if (current_page > 1) {
            current_page--;
            changePage(current_page);
        }
      }
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function nextPage() {
        if (current_page < numPages()) {
            current_page++;
            changePage(current_page);
        }
      }
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function changePage(page) {
        var btn_next = document.getElementById("btn_next");
        var btn_prev = document.getElementById("btn_prev");
        var listing_table = document.getElementById("table_id");
        var page_span = document.getElementById("page");
       
        // Validate page
        if (page < 1) page = 1;
        if (page > numPages()) page = numPages();

        [...listing_table.getElementsByTagName('tr')].forEach((tr)=>{
            tr.style.display='none'; // reset all to not display
        });
        listing_table.rows[0].style.display = ""; // display the title row

        for (var i = (page-1) * records_per_page + 1; i < (page * records_per_page) + 1; i++) {
          if (listing_table.rows[i]) {
            listing_table.rows[i].style.display = ""
          } else {
            continue;
          }
        }
          
        page_span.innerHTML = page + "/" + numPages() + " (Total Number of Rows = " + (l-1) + ") | Number of Rows : ";
        
        if (page == 0 && numPages() == 0) {
          btn_prev.disabled = true;
          btn_next.disabled = true;
          return;
        }

        if (page == 1) {
          btn_prev.disabled = true;
        } else {
          btn_prev.disabled = false;
        }

        if (page == numPages()) {
          btn_next.disabled = true;
        } else {
          btn_next.disabled = false;
        }
      }
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      function numPages() {
        return Math.ceil((l - 1) / records_per_page);
      }
      //------------------------------------------------------------
      
      //------------------------------------------------------------
      window.onload = function() {
        var x = document.getElementById("number_of_rows").value;
        records_per_page = x;
        changePage(current_page);
      };
      //------------------------------------------------------------
    </script>
  </body>
</html>
// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<