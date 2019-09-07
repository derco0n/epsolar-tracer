<?php
header('Content-Type: text/html; charset=UTF-8');
//session_start();
include '/etc/epsolar/databases.php'; //Database-Crendentials in here

//Datenbankoptionen (diese kommen aus den Globalenvariablen, die in ''/media/USBext/Global_www_Options/databases.php'' definiert sind)
$host=$GlobalLoginDBHost;
$user=$GlobalLoginDBUser;
$password=$GlobalLoginDBPass;
$db=$GlobalLoginDBDatabase;

$user_agent = $_SERVER['HTTP_USER_AGENT'];

$mysqli=new mysqli($host, $user, $password, $db); //Mysqli-Objekt erzeugen
// Oh no! A connect_errno exists so the connection attempt failed!
if ($mysqli->connect_errno) {
    // The connection failed. What do you want to do? 
    // You could contact yourself (email?), log the error, show a nice page, etc.
    // You do not want to reveal sensitive information

    // Let's try this:
    echo "Sorry, this website is experiencing problems.";

    // Something you should not do on a public site, but this example will show you
    // anyways, is print out MySQL error related information -- you might log this
    echo "Error: Failed to make a MySQL connection, here is why: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    echo "Error: " . $mysqli->connect_error . "\n";
    
    // You might want to show them something nice, but we will simply exit
    exit;
}

// Perform an SQL query
$sql = "SELECT * from epsolar_log.tbl_reading order by timestamp desc limit 1;";
//echo($sql);
if (!$result = $mysqli->query($sql)) {
	// Oh no! The query failed. 
    echo "Sorry, the website is experiencing problems.";

    // Again, do not do this on a public site, but we'll show you how
    // to get the error information
    echo "Error: Our query failed to execute and here is why: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    echo "Error: " . $mysqli->error . "\n";
    exit;
}

// Phew, we made it. We know our MySQL connection and query 
// succeeded, but do we have a result?
if ($result->num_rows === 0) {
    // Oh, no rows! Sometimes that's expected and okay, sometimes
    // it is not. You decide. In this case, maybe actor_id was too
    // large? 
    echo "We could not find a match for ID $aid, sorry about that. Please try again.";
    exit;
}

// Now, we know only one result will exist in this example so let's 
// fetch it into an associated array where the array's keys are the 
// table's column names
$res = $result->fetch_assoc();
//echo "Sometimes I see " . $res[0] . " " . $res[1] . " on TV.";
foreach($res as $r){
var_dump($r);
}



// The script will automatically free the result and close the MySQL
// connection when it exits, but let's just do it anyways
$result->free();
$mysqli->close();



//Optionen


function leadingzero($wert, $stellen)
	{
	while (strlen($wert) < $stellen)
		{
		$wert="0".$wert;
		}
	return $wert;
	}

//Allgemeines
$Tage=array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
$Monate=array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");


				
?>