<?php
require '../auth/validate.php';

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
		$isTokenValid = validateToken("write:p1", $authParts[1]);
		if( ! $isTokenValid ) {
			echo "Invalid token\n";
			http_response_code(403);
		} else {
			$statusList = json_encode([
					["validtoken" => $isTokenValid],
					["timestamp" => 2132131, "status" => "OK"],
					["timestamp" => 2132132, "status" => "OK"],
					["timestamp" => 2132133, "status" => "OK"]
				], JSON_PRETTY_PRINT);
			echo $statusList;
		}
	}
}
?>
