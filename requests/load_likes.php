﻿<?php
include("../includes/config.php");
session_start();
if($_POST['token_id'] != $_SESSION['token_id']) {
	return false;
}
include("../includes/classes.php");
require_once(getLanguage(null, (!empty($_GET['lang']) ? $_GET['lang'] : $_COOKIE['lang']), 2));
$db = new mysqli($CONF['host'], $CONF['user'], $CONF['pass'], $CONF['name']);
if ($db->connect_errno) {
    echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
}
$db->set_charset("utf8");

$resultSettings = $db->query(getSettings()); 
$settings = $resultSettings->fetch_assoc();

// The theme complete url
$CONF['theme_url'] = $CONF['theme_path'].'/'.$settings['theme'];

$feed = new feed();
$feed->db = $db;
$feed->url = $CONF['url'];
if(isset($_SESSION['username']) && isset($_SESSION['password']) || isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
	$loggedIn = new loggedIn();
	$loggedIn->db = $db;
	$loggedIn->url = $CONF['url'];
	$loggedIn->username = (isset($_SESSION['username'])) ? $_SESSION['username'] : $_COOKIE['username'];
	$loggedIn->password = (isset($_SESSION['password'])) ? $_SESSION['password'] : $_COOKIE['password'];
	
	$verify = $loggedIn->verify();
	
	$feed->user = $verify;
	$feed->username = $verify['username'];
	$feed->id = $verify['idu'];
}

$feed->per_page = $settings['perpage'];
$feed->censor = $settings['censor'];
$feed->smiles = $settings['smiles'];
$feed->c_per_page = $settings['cperpage'];
$feed->time = $settings['time'];
$feed->c_start = 0;
$feed->profile = $_POST['profile'];
$feed->profile_data = $feed->profileData($_POST['profile']);

if(isset($_POST['get_likes'])) {
	$result = $feed->getLikes(0, 2, $_POST['id'], $_POST['extra']);
} else {
	if(ctype_digit($_POST['start'])) {
		if($_POST['type'] == 1) {
			$result = $feed->getLikes($_POST['start'], 1);
		} else {
			$result = $feed->getLikes($_POST['start'], 2, $_POST['query'], $_POST['extra']);
		}
	}
}
echo $result;

mysqli_close($db);
?>