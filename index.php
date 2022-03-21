<?php
require '../auth/validate.php';
require 'energyconfig.php';

$energyConfig = readEnergyConfig();

function myLogRequest() {
	$fileName = "energy.log";
	if( filesize($fileName) > 10000 ) {
		$myfile=fopen($fileName, "w") or die("Unable to open file");
		fwrite($myfile, "Truncated\n") or die("fwrite error");
		fclose($myfile);
	}
	$myfile=fopen($fileName, "a") or die("Unable to open file");
	fwrite($myfile, "Headers:" . print_r($_SERVER, true) . "\n") or die("fwrite error");
	fwrite($myfile, "Request:" . print_r($_REQUEST, true) . "\n") or die("fwrite error");
	fclose($myfile);
}

myLogRequest();

if( ! isset($_SERVER['HTTP_AUTHORIZATION']) ) {
	echo "Missing authorization header\n";
	http_response_code(403);
} else {
	$authParts = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
	if( count($authParts) != 2 || $authParts[0] !== "Bearer" ) {
		echo "Need bearer token authorization\n";
		http_response_code(403);
	} else {
		if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			if ( ! validateToken("read:p1", $authParts[1]) ) {
				echo "Invalid token\n";
				http_response_code(403);
			} else {
				$statusList = json_encode([
						["db.host" => getDbHost($energyConfig)],
						["db.user" => getDbUser($energyConfig)],
						["db.name" => getDbName($energyConfig)]
					], JSON_PRETTY_PRINT);
				echo $statusList;
			}
		} else if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			if ( ! validateToken("write:p1", $authParts[1]) ) {
				echo "Invalid token\n";
				http_response_code(403);
			} else {
				echo "DB connect\n";
				$link = mysqli_connect(getDbHost($energyConfig), getDbUser($energyConfig), getDbPassword($energyConfig), getDbName($energyConfig)) or die ('Could not connect to database: ' . mysql_error());
				$current = $_REQUEST['current'];
				$datetime = $_REQUEST['datetime'];
				echo "current: ${current}\n";
				echo "datetime: ${datetime}\n";
				$query = "insert into current_max (current, datetime) values (${current}, '${datetime}');select min(current) from current_max; select max(current) from current_max;select * from current_max;";
				if ( mysqli_multi_query($link, $query) ) {
					echo "First query OK\n";
					do {
						if ( $result = mysqli_store_result($link) ) {
							while( $row = mysqli_fetch_row($result) ) {
								echo "ROW: ";
								print_r($row);
							}
						}
					} while ( mysqli_more_results($link) && mysqli_next_result($link) );
				} else {
					echo "First query failed\n";
				}
			}
		}
	}
}
?>
