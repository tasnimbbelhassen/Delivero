<?php

session_start();

echo "<h2>Session Configuration Test</h2>";
echo "<pre>";


echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Cookie Params:\n";
print_r(session_get_cookie_params());


echo "\nCurrent Session Data:\n";
print_r($_SESSION);


$_SESSION['test_timestamp'] = time();
echo "\nTest timestamp written: " . $_SESSION['test_timestamp'];

echo "</pre>";

echo "<h3>Cookies sent by browser:</h3>";
print_r($_COOKIE);
?>