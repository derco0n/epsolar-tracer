<?php
header('Content-Type: text/html; charset=UTF-8');
//session_start();
include '/etc/epsolar/databases.php'; //Database-Crendentials in here
include './sql-class.php';

//Databaseoptions (coming out of the settings in /etc/epsolar/databases.php
$host=$GlobalLoginDBHost;
$user=$GlobalLoginDBUser;
$password=$GlobalLoginDBPass;
$db=$GlobalLoginDBDatabase;

$mydbcon=new mysql($host, $user, $password, $db);
$sqliobj=$mydbcon->getSQLIObject();

//Functions
function getdata()
{
	//TESTING (later to be filled my DATABASE)
	$myreturn = array(
		"[0, 100, 0, -100, 0, 100]",
		"[100, 0, -100, 0, 100, 0]",
		"[0, -100, 0, 100, 0, -100]",
		"[-100, 0, 100, 0, -100, 0]",
		"[12.5, 25, 37.5, 50, 62.5, 75]"
	);
	
	
	//TESTING END
    
    return $myreturn;
}

function getlinelabels(){
	//TESTING (later to be filled my DATABASE)
	$myreturn = array(
	"Dies",
	"sind",
	"bloß",
	"Demo",
	"Daten!"
	);
	//TESTING END
	
	return $myreturn;
}

function getaxislabels(){
	//TESTING
	$myreturn="['00:00 Uhr', '06:00', '09:00', '12:00', '15:00', '18:00']";
	//TESTING END

	return $myreturn;
}

//Datadefinitions

//initialize with dummy-data
$datamode=0;
$timeframe="";

if (isset($_GET['datamode'])){
/*
datamodes:
1=chart
2=table
*/	
	switch($_GET['datamode']) {
		case "1":
			$datamode=1;
			break;
		case "2":
			$datamode=2;
			break;
	}	
}

if (isset($_GET['timeframe'])){
	//Valid options: today, week, month, year
	if ($_GET['timeframe'] == "today" || $_GET['timeframe'] == "week" || $_GET['timeframe'] == "month" || $_GET['timeframe'] == "year" || $_GET['timeframe'] =="allraw") {
		$timeframe=$_GET['timeframe'];
	}
	else {
		echo("Ung&uuml;ltiger Zeitraum angegeben.");
		exit;
	}
}

//Generate SQL-Queries by Timeframe
	switch ($timeframe) {
		case "today":
			echo("<h3>Daten von Heute:</h3>");
			$mintimestamp=date("Y-m-d")." 00:00:00"; 
			$maxtimestamp=date("Y-m-d")." 23:59:59"; 
			
			$statement="SELECT 
				`timestamp` as 'Zeitpunkt',
				concat(`con-temp-main`, ' °C') as 'Controller-Temperatur',
				concat(`con-temp-heatsink`, ' °C') as 'Controller-K&uuml;hler-Temperatur',
				concat(`pv-voltage`, ' V') as 'Generator-Spannung',
				concat(`pv-current`, ' A') as 'Generator-Strom',
				concat(`pv-power`, ' W') as 'Generator-Leistung',
				concat(`bat-voltage`, ' V') as 'Batterie-Spannung',
				concat(`bat-current`, ' A') as 'Batterie-Strom',
				concat(`bat-power`, ' W') as 'Batterie-Leistung',
				concat(`bat-perc`, ' %') as 'Batterie-Ladestand',
				concat(`bat-temp`, ' °C') as 'Batterie-Temperatur',
				concat(`load-voltage`, ' V') as 'Lastausgang-Spannung',
				concat(`load-current`, ' A') as 'Lastausgang-Strom',
				concat(`load-power`, ' W') as 'Lastausgang-Leistung'	
				from epsolar_log.tbl_reading 
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				ORDER BY `timestamp` DESC";
			
			break;
		case "week":
			echo("<h3>Daten dieser Woche:</h3>");			
			$mintimestamp=date("Y-m-d", strtotime('-1 week monday 00:00:00')); 
			$maxtimestamp=date("Y-m-d", strtotime('sunday 23:59:59'));
			$statement="SELECT
				concat(LEFT(`timestamp`, CHAR_LENGTH(`timestamp`) -5), '00-59') as 'Zeitpunkt des Tages',				
				concat(ROUND(AVG(`con-temp-main`),2), ' °C') as 'Mittlere Controller-Temperatur',
				concat(ROUND(AVG(`con-temp-heatsink`),2), ' °C') as 'Mittlere Controller-K&uuml;hler-Temperatur',
				concat(ROUND(AVG(`pv-voltage`),2), ' V') as 'Mittlere Generator-Spannung',
				concat(ROUND(AVG(`pv-current`),2), ' A') as 'Mittlere Generator-Strom',
				concat(ROUND(AVG(`pv-power`),2), ' W') as 'Mittlere Generator-Leistung',
				concat(ROUND(AVG(`bat-voltage`),2), ' V') as 'Mittlere Batterie-Spannung',
				concat(ROUND(AVG(`bat-current`),2), ' A') as 'Mittlere Batterie-Strom',
				concat(ROUND(AVG(`bat-power`),2), ' W') as 'Mittlere Batterie-Leistung',
				concat(ROUND(AVG(`bat-perc`),2), ' %') as 'Mittlere Batterie-Ladestand',
				concat(ROUND(AVG(`bat-temp`),2), ' °C') as 'Mittlere Batterie-Temperatur',
				concat(ROUND(AVG(`load-voltage`),2), ' V') as 'Mittlere Lastausgang-Spannung',
				concat(ROUND(AVG(`load-current`),2), ' A') as 'Mittlere Lastausgang-Strom',
				concat(ROUND(AVG(`load-power`),2), ' W') as 'Mittlere Lastausgang-Leistung'	
				from epsolar_log.tbl_reading
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				GROUP BY date_format( `timestamp`, '%Y%m%d%H' )
				ORDER BY `timestamp` DESC";			
			break;
		case "month":
			echo("<h3>Daten dieses Monats:</h3>");
			$mintimestamp=date("Y-m-d", strtotime('first day of this month')); 
			$maxtimestamp=date("Y-m-d", strtotime('last day of this month'));
			$statement="SELECT
				concat(LEFT(`timestamp`, CHAR_LENGTH(`timestamp`) -8), '') as 'Tag des Monats',
				concat(ROUND(AVG(`con-temp-main`),2), ' °C') as 'Mittlere Controller-Temperatur',
				concat(ROUND(AVG(`con-temp-heatsink`),2), ' °C') as 'Mittlere Controller-K&uuml;hler-Temperatur',
				concat(ROUND(AVG(`pv-voltage`),2), ' V') as 'Mittlere Generator-Spannung',
				concat(ROUND(AVG(`pv-current`),2), ' A') as 'Mittlere Generator-Strom',
				concat(ROUND(AVG(`pv-power`),2), ' W') as 'Mittlere Generator-Leistung',
				concat(ROUND(AVG(`bat-voltage`),2), ' V') as 'Mittlere Batterie-Spannung',
				concat(ROUND(AVG(`bat-current`),2), ' A') as 'Mittlere Batterie-Strom',
				concat(ROUND(AVG(`bat-power`),2), ' W') as 'Mittlere Batterie-Leistung',
				concat(ROUND(AVG(`bat-perc`),2), ' %') as 'Mittlere Batterie-Ladestand',
				concat(ROUND(AVG(`bat-temp`),2), ' °C') as 'Mittlere Batterie-Temperatur',
				concat(ROUND(AVG(`load-voltage`),2), ' V') as 'Mittlere Lastausgang-Spannung',
				concat(ROUND(AVG(`load-current`),2), ' A') as 'Mittlere Lastausgang-Strom',
				concat(ROUND(AVG(`load-power`),2), ' W') as 'Mittlere Lastausgang-Leistung'	
				from epsolar_log.tbl_reading
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				GROUP BY date_format( `timestamp`, '%Y%m%d' )
				ORDER BY `timestamp` DESC";
			break;
		case "year":
			echo("<h3>Daten dieses Jahres:</h3>");
			$mintimestamp=date("Y-m-d", strtotime(date('Y-01-01'))); 
			$maxtimestamp=date("Y-m-d", strtotime(date('Y-12-31')));
			
			$statement="SELECT
				CONCAT(WEEK(`timestamp`), ' / ', YEAR(`timestamp`)) as 'Kalenderwoche',
				concat(ROUND(AVG(`con-temp-main`),2), ' °C') as 'Mittlere Controller-Temperatur',
				concat(ROUND(AVG(`con-temp-heatsink`),2), ' °C') as 'Mittlere Controller-K&uuml;hler-Temperatur',
				concat(ROUND(AVG(`pv-voltage`),2), ' V') as 'Mittlere Generator-Spannung',
				concat(ROUND(AVG(`pv-current`),2), ' A') as 'Mittlere Generator-Strom',
				concat(ROUND(AVG(`pv-power`),2), ' W') as 'Mittlere Generator-Leistung',
				concat(ROUND(AVG(`bat-voltage`),2), ' V') as 'Mittlere Batterie-Spannung',
				concat(ROUND(AVG(`bat-current`),2), ' A') as 'Mittlere Batterie-Strom',
				concat(ROUND(AVG(`bat-power`),2), ' W') as 'Mittlere Batterie-Leistung',
				concat(ROUND(AVG(`bat-perc`),2), ' %') as 'Mittlere Batterie-Ladestand',
				concat(ROUND(AVG(`bat-temp`),2), ' °C') as 'Mittlere Batterie-Temperatur',
				concat(ROUND(AVG(`load-voltage`),2), ' V') as 'Mittlere Lastausgang-Spannung',
				concat(ROUND(AVG(`load-current`),2), ' A') as 'Mittlere Lastausgang-Strom',
				concat(ROUND(AVG(`load-power`),2), ' W') as 'Mittlere Lastausgang-Leistung'	
				from epsolar_log.tbl_reading
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				GROUP BY WEEK( `timestamp`)
				ORDER BY `timestamp` DESC";
			break;
		case "allraw":
			echo("<h3>Alle (Roh-)Daten:</h3>");
			$statement="SELECT 
				`timestamp` as 'Zeitpunkt',
				concat(`con-temp-main`, ' °C') as 'Controller-Temperatur',
				concat(`con-temp-heatsink`, ' °C') as 'Controller-K&uuml;hler-Temperatur',
				concat(`pv-voltage`, ' V') as 'Generator-Spannung',
				concat(`pv-current`, ' A') as 'Generator-Strom',
				concat(`pv-power`, ' W') as 'Generator-Leistung',
				concat(`bat-voltage`, ' V') as 'Batterie-Spannung',
				concat(`bat-current`, ' A') as 'Batterie-Strom',
				concat(`bat-power`, ' W') as 'Batterie-Leistung',
				concat(`bat-perc`, ' %') as 'Batterie-Ladestand',
				concat(`bat-temp`, ' °C') as 'Batterie-Temperatur',
				concat(`load-voltage`, ' V') as 'Lastausgang-Spannung',
				concat(`load-current`, ' A') as 'Lastausgang-Strom',
				concat(`load-power`, ' W') as 'Lastausgang-Leistung'	
				from epsolar_log.tbl_reading				
				ORDER BY `timestamp` DESC";
			break;
	}

/*
var_dump($timeframe);
var_dump($datamode);
*/

//Main

echo("<html>\r\n");
echo("\t<head>\r\n");
echo("\t\t<link href='epsolar_main.css' rel='stylesheet'>\r\n");
echo("\t</head>\r\n");
echo("\t<body>\r\n");
//Menüif ($datamode==1){
echo("
		<p>
			<div id='menu'>
				<ul>
					<li class='topmenu'>
						<a href=''>Diagramme</a>
						<ul>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=today'>Heute</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=week'>Diese Woche</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=month'>Diesen Monat</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=year'>Dieses Jahr</a></li>
						</ul>
					</li>
					<li class='topmenu'>
						<a href=''>Tabellen</a>
						<ul>
							<li class='submenu'><a href='./history.php?datamode=2&timeframe=today'>Heute</a></li>
							<li class='submenu'><a href='./history.php?datamode=2&timeframe=week'>Diese Woche</a></li>
							<li class='submenu'><a href='./history.php?datamode=2&timeframe=month'>Diesen Monat</a></li>
							<li class='submenu'><a href='./history.php?datamode=2&timeframe=year'>Dieses Jahr</a></li>
							<li class='submenu'><a href='./history.php?datamode=2&timeframe=allraw'>Alle Rohdaten (VORSICHT viele Daten!)</a></li>
						</ul>
					</li>        
				</ul>
			</div>

		</p>");

echo("
		<p>
			<div id='diagram'>");


if ($datamode==1){
	//Datamode for chart (diagramm) is set. Display Canvas
	
	//Render diagram
	//Import chart.js-library
	echo("
				<script src='./node_modules/chart.js/dist/Chart.js'></script>");

	//define new canvas-tag (where to draw)
	echo("		<br />
				<br />
				<canvas id='sunchart' aria-label='sunchart' role='img'></canvas>");
	
	$datalabels=getaxislabels();
	
	//Define Chartoptions
	$chartoptions="{
			responsive: true,
			aspectRatio: 4.5,
			elements: {
				line: {
					stacked: true,
					tension: 0.3 //disables bezier curves when set to zero
				}
			},
			scales: {			
				yAxes: [{				
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}";




	//Colors
	$linecolors=array(
		//Green
		"rgba(0, 255, 0, 1)",
		"rgba(0, 200, 0, 0.2)",

		//RED
		"rgba(255, 0, 0, 1)",
		"rgba(200, 0, 0, 0.2)",

		//Blue
		"rgba(0, 0, 255, 1)",
		"rgba(0, 0, 200, 0.2)",

		//Yellow
		"rgba(255, 255, 0, 1)",
		"rgba(200, 200, 0, 0.2)",

		//Pink
		"rgba(255, 0, 255, 1)",
		"rgba(200, 0, 200, 0.2)",

		//Black	
		"rgba(255, 255, 255, 1)",
		"rgba(200, 200, 200, 0.2)",

		//Grey
		"rgba(160, 160, 160, 1)",
		"rgba(128, 128, 128, 0.2)",
	);

	$maxdatalines=sizeof($linecolors)/2; //Maximum drawable line due to defined colors...
	// echo($linecolors[0]); //DEBUG

	//Data-Labels
	$charlabels=getlinelabels();
	//RAW-Data
	$dataarray=getdata();

	echo(sizeof($dataarray));
	if (sizeof($dataarray) <= $maxdatalines)
	{
		//define Data to render
		$chartdata="{
				labels: ".$datalabels.",
				datasets: [";

	//Prepare each dataset
		$count=0;
		$colorcount=0;
		foreach ($dataarray as $datarow)
			{
					if ($count != 0 && $count<sizeof($dataarray)){
						//Not first or last entry in the dataarray
							//Therefore after the first and before the last one
								//put a ", " after each dataset
						$chartdata=$chartdata.", ";

					}
					$chartdata=$chartdata."{label: '".$charlabels[$count]."',	data: ".$datarow.",
					 borderColor: [
						'".$linecolors[$colorcount]."'
					],
					backgroundColor: [
						'".$linecolors[$colorcount+1]."'
					],           
					borderWidth: 1
				}";
			$count++;
			$colorcount=$colorcount+2;
			}

		$chartdata=$chartdata."]}";

	}

	//echo($chartdata); //DEBUG


	//define script to draw chart in canvas
	echo("
					<script>
						var ctx = document.getElementById('sunchart').getContext('2d');
						var sunchart = new Chart(ctx, {
						type: 'line',
						data: ".$chartdata.",
						options: ".$chartoptions."
						});
					</script>
				");
	
	
	
}
else if ($datamode==2){
	echo("		<br />
				<br />
				<br />");
	

	
	//Datamode for table is set. Render Table
	//Make a Table-Query;
	$mydbcon->tablequery($statement);
	
}
else {
	//No Data-Presentation-Mode selected...
	echo("<br /><br /><br />Bitte eine Darstellung ausw&auml;hlen.");
	
}





echo("
			</div>
		</p>");

echo("
	</body>");
echo("
</html>");
?>
