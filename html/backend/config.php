<?php

### IF YOU ARE GOING TO USE THE CHARACTER ' IN ANY OF THE OPTIONS, ESCAPE IT LIKE THIS: \' ###

//remote  MySQL details
define('global_mysql_server', '001.mysql.db.fge.5hosting.com');
define('global_mysql_user', 'u297_newlifesymb');
define('global_mysql_password', 'Dressler123!');
define('global_mysql_database', 'db297_newlifesymb');
define('global_lifesymb_facebook_webapp_APIkey', '1420863004804132');

//local MySQL details
/*define('global_mysql_server', 'localhost');
define('global_mysql_user', 'lifesymb');
define('global_mysql_password', '25100152');
define('global_mysql_database', 'lifesymbreservation');*/

// Salt for password encryption. Changing it is recommended. Use 9 random characters
// This MUST be 9 characters, and must NOT be changed after users have been created
define('global_salt', 'k4i8pa2m5');

// Days to remember login (if the user chooses to remember it)
define('global_remember_login_days', '180');

// Title. Used in page title and header
define('global_title', 'LifeSymb training session reservation');

// Organization. Used in page title and header, and as sender name in reservation reminder emails
define('global_organization', 'LifeSymb');

// Secret code. Can be used to only allow certain people to create a user
// Set to '0' to disable
define('global_secret_code', '0');

// Email address to webmaster. Shown to users that want to know the secret code
// To avoid spamming, JavaScript & Base64 is used to show email addresses when not logged in
define('global_webmaster_email', 'info@lifesymb.com');

// Set to '1' to enable reservation reminders. Adds an option in the control panel
// Check out the wiki for instructions on how to make it work
define('global_reservation_reminders', '0');

// Reservation reminders are sent from this email
// Should be an email address that you own, and that is handled by your web host provider
define('global_reservation_reminders_email', 'info@lifesymb.com');

// Code to run the reservation reminders script over HTTP
// If reservation reminders are enabled, this MUST be changed. Check out the wiki for more information
define('global_reservation_reminders_code', '1234');

// Full URL to web site. Used in reservation reminder emails
define('global_url', 'http://lifesymb.com/backend/lifesymbreservation/');

// Currency (short format). Price per reservation can be changed in the control panel
// Currency should not be changed after reservations have been made (of obvious reasons)
define('global_currency', 'SEK');

// How many weeks forward in time to allow reservations
define('global_weeks_forward', '2');

// Possible reservation times. Use the same syntax as below (TimeFrom-TimeTo)
$global_times = array('08-09', '09-10', '10-11', '11-12', '12-13', '13-14', '14-15', '15-16', '16-17', '17-18', '18-19');
$monday_times = array();
$tuesday_times = array('08-09' => 'Other', '09-10' => 'Other', '10-11' => 'Other', '11-12' => 'Other', '13-14' => 'Other', '14-15' => 'Other', '15-16' => 'Other', '16-17' => 'Other', '17-18' => 'Other');
$wednesday_times = array('17-18' => '4D', '18-19'  => '4D');
$thursday_times = array('08-09' => 'Other', '09-10' => 'Other', '10-11' => 'Other', '11-12' => 'Other', '13-14' => 'Other', '14-15' => 'Other', '15-16' => 'Other', '16-17' => 'Other', '17-18' => 'Other');
$friday_times = array('08-09' => 'Other', '09-10' => 'Other', '10-11' => 'Other', '11-12' => 'Other');
$saturday_times = array('08-09' => 'Other', '09-10' => 'Other', '10-11' => 'Other', '11-12' => 'Other', '13-14' => '4D', '14-15' => '4D');
$sunday_times = array();
$schedule_times = array(1 => $monday_times, 2 => $tuesday_times, 3 => $wednesday_times, 4 => $thursday_times,
    5 => $friday_times, 6 => $saturday_times, 7 => $sunday_times);

?>
