<?php
require_once('SecureSessionHandler.php');

session_set_save_handler(
	new \SecureSessionNS\SecureSessionHandler(),
	true
);


// start the my hadler
session_start();

if (isset($_GET['set'])) {
	$_SESSION['name'] = 122.455;
	if(!isset($_SESSION['time'])) {
		$_SESSION['time'] = 0;
	}else {
		$_SESSION['time'] += 1;
	}
}

var_dump($_SESSION);


