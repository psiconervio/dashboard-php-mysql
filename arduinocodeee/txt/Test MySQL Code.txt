//---------------------------------------- Test
CREATE TABLE `esp32_table_test` (
    `id` varchar(255) NOT NULL,
    `temperature` float(10,2) NOT NULL,
    `humidity` int(3) NOT NULL,
    `status_read_sensor_dht11` varchar(255) NOT NULL,
    `LED_01` varchar(255) NOT NULL,
    `LED_02` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
//---------------------------------------- 

//---------------------------------------- 
INSERT INTO `esp32_table_test`(`id`, `temperature`, `humidity`, `status_read_sensor_dht11`, `LED_01`, `LED_02`) VALUES ('esp32_01','0.00','0','SUCCESS','OFF','OFF')
//---------------------------------------- 