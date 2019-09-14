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
//function getdata($mintimestamp, $maxtimestamp, $con, $type="voltages")
function getdata($timeframe, $con, $type="voltages")
{
	$wholedata=array();	
	
	$fieldnames=array();
	
	switch ($timeframe) {
		case "today":			
			$mintimestamp=date("Y-m-d")." 00:00:00"; 
			$maxtimestamp=date("Y-m-d")." 23:59:59"; 
			$querytimestamp="CONCAT(DATE_FORMAT(`timestamp`,'%H:%i'), ' Uhr') as 'Zeitpunkt', ";
			$querygroupby="";
			break;
		case "week":
			//echo("<h3>Daten dieser Woche:</h3>");			
			$mintimestamp=date("Y-m-d", strtotime('-1 week monday 00:00:00')); 
			$maxtimestamp=date("Y-m-d", strtotime('sunday 23:59:59'));
			$querytimestamp="CONCAT(DATE_FORMAT(`timestamp`,'%W:%H'), ' Uhr') as 'Zeitpunkt', ";
			$querygroupby="";
			break;
		case "month":
			//echo("<h3>Daten dieses Monats:</h3>");
			$mintimestamp=date("Y-m-d", strtotime('first day of this month')); 
			$maxtimestamp=date("Y-m-d", strtotime('last day of this month'));
			break;
		case "year":
			//echo("<h3>Daten dieses Jahres:</h3>");
			$mintimestamp=date("Y-m-d", strtotime(date('Y-01-01'))); 
			$maxtimestamp=date("Y-m-d", strtotime(date('Y-12-31')));
			break;		
		}	
	
	switch($type){
		case "all":
			$queryfields="			
			`pv-voltage` as 'Generator-Spannung [V]',
			`bat-voltage` as 'Batterie-Spannung [V]',
			`load-voltage` as 'Lastausgang-Spannung [V]',
			`pv-power` as 'Generator-Leistung [W]',
			`bat-power` as 'Batterie-Leistung [W]',
			`load-power` as 'Lastausgang-Leistung [W]',
			`pv-current` as 'Generator-Strom [A]',
			`bat-current` as 'Batterie-Strom [A]',
			`load-current` as 'Lastausgang-Strom [A]',
			`bat-perc` as 'Batterieladestand [%]',
			`bat-temp` as 'Batterie-Temperatur [°C]',
			`con-temp-main` as 'Controller-Temperatur [°C]',
			`con-temp-heatsink` as 'Controller-Heatsink-Temperatur [°C]'
			";
			break;
		case "voltages":
			$queryfields="			
			`pv-voltage` as 'Generator-Spannung [V]',
			`bat-voltage` as 'Batterie-Spannung [V]',
			`load-voltage` as 'Lastausgang-Spannung [V]'
			";
			break;
		case "power":
			$queryfields="			
			`pv-power` as 'Generator-Leistung [W]',
			`bat-power` as 'Batterie-Leistung [W]',
			`load-power` as 'Lastausgang-Leistung [W]'
			";
			break;
		case "current":
			$queryfields="
			DATE_FORMAT(`timestamp`,'%H:%i') as 'Zeitpunkt',
			`pv-current` as 'Generator-Strom [A]',
			`bat-current` as 'Batterie-Strom [A]',
			`load-current` as 'Lastausgang-Strom [A]'
			";
			break;
		case "battery":
			$queryfields="
			DATE_FORMAT(`timestamp`,'%H:%i') as 'Zeitpunkt',
			`bat-perc` as 'Batterieladestand [%]',
			`bat-temp` as 'Batterie-Temperatur [°C]'
			";
			break;
	}
	
	
	
	
	$statement="
	SELECT "
		.$querytimestamp.
		$queryfields."
	from 
	epsolar_log.tbl_reading 
	WHERE `timestamp` 
	BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."' 
	".$querygroupby."
	ORDER BY `timestamp` ASC";	 
	
	$resdata= $con->arrayquery($statement, False, True); //2D-Array [rows][fields]	
	
	//Determine the fieldcount
	$numfields=sizeof($resdata[0]); //Result contains fieldnames
	
	//Process the field names in the first line of the resultset
	$fnames=array();
	$count=1; //Skip the first name as its the timestamp
	while ($count < $numfields){		
		$fnames [] = $resdata[0][$count];
		$count++;
	}
	
	//var_dump($resdata); //DEBUG
	
	
	//Fetch the data from the result but cutting of the fieldnames
	$data=array();
	$count=1; //Skip the first line as that are the fieldnames
	while ($count < sizeof($resdata)){		
		$data [] = $resdata[$count];
		$count++;
	}
	
	//var_dump($data); //DEBUG
	
	$fieldnames=$fnames;
	
	//var_dump($fieldnames); //DEBUG
	
	$wholedata [] = $fieldnames; //Add Fieldnames to wholedata
		
	$datalines=array(); //Rows -> lines for datavalaues
	$xnames=array(); //Rows -> lines for x-axis-names
	
	
	$count=0;
	while ($count < $numfields){
		$tempcol=array_column($data, $count);
		$tempvalcol=array(); //numeric-values of that line
			foreach($tempcol as $tcval){
				//Convert each value saved as string into float
				if ($count==0) { 
					//Names
					$tempvalcol [] = $tcval;  //Use String data
				}
				else if ($count >0){
					//Datavalues
					$tempvalcol [] = floatval($tcval); //Convert to float
				}
			}
		if ($count==0){
			$xnames[] = json_encode($tempvalcol);
		}
		else if ($count>0) {
			$datalines[] = json_encode($tempvalcol);		
		}
		
		$count++;
	}
	
	$wholedata [] = $xnames; // Add names of the X-Axis to wholedata

	$wholedata [] = $datalines; //Add datalines to wholedata
	
	//var_dump($wholedata[1]); //DEBUG
	
	//return $datalines;
	return $wholedata; //0=Fieldnames, 1=x-axis-names, 2=data
	
}


//Datadefinitions

//initialize with dummy-data
$datamode=0;
$cdtype="voltages";
$timeframe="";

if (isset($_GET['type'])){
	/*
typess:
1=voltages
2=power
3=current
4=battery
*/
	switch($_GET['type']) {
		case "0":
			$cdtype="all";
			break;
		case "1":
			$cdtype="voltages";
			break;
		case "2":
			$cdtype="power";
			break;
		case "3":
			$cdtype="current";
			break;
		case "4":
			$cdtype="battery";
			break;
	}	
}

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
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=today&type=1'>Heute (Spannungen)</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=today&type=2'>Heute (Leistung)</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=today&type=3'>Heute (Str&ouml;me)</a></li>							
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=today&type=4'>Heute (Batterie)</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=today&type=0'>Heute (Alles)</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=week'&type=1>Diese Woche (Spannungen)</a></li>
	"./*

							<li class='submenu'><a href='./history.php?datamode=1&timeframe=week'>Diese Woche</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=month'>Diesen Monat</a></li>
							<li class='submenu'><a href='./history.php?datamode=1&timeframe=year'>Dieses Jahr</a></li>
	*/
	 "
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
		
		//Violett
		"rgba(128, 0, 196, 1)",
		"rgba(96, 0, 128, 0.2)",
		
		//Cyan
		"rgba(0, 160, 160, 1)",
		"rgba(0, 128, 128, 0.2)",
		
		//Orange
		"rgba(255, 128, 00, 1)",
		"rgba(128, 96, 0, 0.2)",	
		
		//Brown
		"rgba(160, 96, 0, 1)",
		"rgba(96, 64, 0, 0.2)",
		
		//Dark-Green
		"rgba(0, 160, 0, 0.2)",
		"rgba(0, 96, 0, 0.2)",
		
		//Dark-RED
		"rgba(196, 0, 0, 1)",
		"rgba(160, 0, 0, 0.2)",
		
		//Dark-Blue
		"rgba(0, 0, 160, 1)",
		"rgba(0, 0, 128, 0.2)",
		
		//Black	
		"rgba(0, 0, 0, 1)",
		"rgba(40, 40, 40, 0.2)",

		//Grey
		"rgba(160, 160, 160, 1)",
		"rgba(128, 128, 128, 0.2)",
		
		
	);

	$maxdatalines=sizeof($linecolors)/2; //Maximum drawable line due to defined colors...
	// echo($linecolors[0]); //DEBUG


	
	//QUERY-Database an get result prepared for chart
	//$chardata=getdata($mintimestamp, $maxtimestamp, $mydbcon, $cdtype); //returns: 0=Fieldnames, 1=x-axis-names, 2=data
	$chardata=getdata($timeframe, $mydbcon, $cdtype); //returns: 0=Fieldnames, 1=x-axis-names, 2=data
	
	//var_dump($chardata); //DEBUG
	
	//Axis-labels
	//$datalabels=getaxislabels($mintimestamp, $maxtimestamp, $mydbcon);
	$datalabels=$chardata[1][0];	
	
	//Data-Labels
	//$charlabels=getlinelabels($mintimestamp, $maxtimestamp, $mydbcon)[0];
	$charlabels=$chardata[0];
	
	//RAW-Data
	//$dataarray=getdata($mintimestamp, $maxtimestamp, $mydbcon)[1];
	$dataarray=$chardata[2];

	//echo(sizeof($dataarray)); //DEBUG
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
	
	//Fetch data
	

	
	
	
	//$mydbcon->tablequery($statement);
	
	
}
else if ($datamode==2){
	echo("		<br />
				<br />
				<br />");
	

	
	//Datamode for table is set. Render Table
	//Make a Table-Query;
	//Generate SQL-Queries by Timeframe
	switch ($timeframe) {
		case "today":
			echo("<h3>Daten von Heute:</h3>");
			$mintimestamp=date("Y-m-d")." 00:00:00"; 
			$maxtimestamp=date("Y-m-d")." 23:59:59"; 
			
			$statement="SELECT				
				DATE_FORMAT(`timestamp`, '%H:%i') as 'Zeitpunkt',
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
