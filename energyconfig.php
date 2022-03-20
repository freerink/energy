<?php
function readEnergyConfig() {
	// echo "energy DOC_ROOT: " . $_SERVER["DOCUMENT_ROOT"] . "\n";
	// Only works if we use a single depth folder structure
	$json_string=file_get_contents("../../config/energy-config.json") or die("Unable to open file");
	$config = json_decode($json_string, true);
	return $config;
}

function getDbHost($config) {
	return $config["db"]["host"];
}

function getDbName($config) {
	return $config["db"]["name"];
}

function getDbUser($config) {
	return $config["db"]["user"];
}

function getDbPassword($config) {
	return $config["db"]["password"];
}

// echo "config: " . readConfig()["db"]["host"];
?>
