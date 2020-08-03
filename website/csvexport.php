<?php
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
			$querytimestamp="DATE_FORMAT(`timestamp`,'%H:%i'), ' Uhr') as 'Zeitpunkt', ";
			$querygroupby="";
			break;
		case "week":
			//echo("<h3>Daten dieser Woche:</h3>");			
			$mintimestamp=date("Y-m-d", strtotime('-1 week monday 00:00:00')); 
			$maxtimestamp=date("Y-m-d", strtotime('sunday 23:59:59'));
			$querytimestamp="DATE_FORMAT(`timestamp`,'%W:%H'), ' Uhr') as 'Zeitpunkt', ";
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
3=csv
*/	
	switch($_GET['datamode']) {
		case "1":
			$datamode=1;
			break;
		case "2":
			$datamode=2;
			break;
		case "3":
			$datamode=3;
			break;
	}	
}

if (isset($_GET['timeframe'])){
	//Valid options: today, week, month, year
	if ($_GET['timeframe'] == "today" || $_GET['timeframe'] == "week" || $_GET['timeframe'] == "month" || $_GET['timeframe'] == "year" || $_GET['timeframe'] =="allraw") {
		$timeframe=$_GET['timeframe'];
	}
	else {
		exit;
	}
}



/*
var_dump($timeframe);
var_dump($datamode);
*/

//Main
if ($datamode==3){
	
	//Datamode for CSV is set. Render Table
	//Make a CSV-Query;
	//Generate SQL-Queries by Timeframe
	switch ($timeframe) {
		case "today":
			$mintimestamp=date("Y-m-d")." 00:00:00"; 
			$maxtimestamp=date("Y-m-d")." 23:59:59"; 
			$filename = "solar-export_daily_" . date('Y-m-d') . ".csv";
			$statement="SELECT				
				DATE_FORMAT(`timestamp`, '%H:%i') as 'Zeitpunkt',
				`con-temp-main` as 'Controller-Temperatur (°C)',
				`con-temp-heatsink` as 'Controller-Kühler-Temperatur (°C)',
				`pv-voltage` as 'Generator-Spannung (V)',
				`pv-current` as 'Generator-Strom (A)',
				`pv-power` as 'Generator-Leistung (W)',
				`bat-voltage` as 'Batterie-Spannung (V)',
				`bat-current` as 'Batterie-Strom (A)',
				`bat-power` as 'Batterie-Leistung (W)',
				`bat-perc` as 'Batterie-Ladestand (%)',
				`bat-temp` as 'Batterie-Temperatur (°C)',
				`load-voltage` as 'Lastausgang-Spannung (V)',
				`load-current` as 'Lastausgang-Strom (A)',
				`load-power` as 'Lastausgang-Leistung (W)'	
				from epsolar_log.tbl_reading 
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				ORDER BY `timestamp` DESC";
			
			break;
		case "week":
			$filename = "solar-export_weeks_" . date('Y-m-d') . ".csv";
			$mintimestamp=date("Y-m-d", strtotime('-1 week monday 00:00:00')); 
			$maxtimestamp=date("Y-m-d", strtotime('sunday 23:59:59'));
			$statement="SELECT
				LEFT(`timestamp`, CHAR_LENGTH(`timestamp`) -4) as 'Stunde des Tages',				
				ROUND(AVG(`con-temp-main`),2) as 'Mittlere Controller-Temperatur (°C)',
				ROUND(AVG(`con-temp-heatsink`),2) as 'Mittlere Controller-Kühler-Temperatur (°C)',
				ROUND(AVG(`pv-voltage`),2) as 'Mittlere Generator-Spannung (V)',
				ROUND(AVG(`pv-current`),2) as 'Mittlerer Generator-Strom (A)',
				ROUND(AVG(`pv-power`),2) as 'Mittlere Generator-Leistung (W)',
				ROUND(AVG(`bat-voltage`),2) as 'Mittlere Batterie-Spannung (V)',
				ROUND(AVG(`bat-current`),2) as 'Mittlerer Batterie-Strom (A)',
				ROUND(AVG(`bat-power`),2) as 'Mittlere Batterie-Leistung (W)',
				ROUND(AVG(`bat-perc`),2) as 'Mittlere Batterie-Ladestand (%)',
				ROUND(AVG(`bat-temp`),2) as 'Mittlere Batterie-Temperatur (°C)',
				ROUND(AVG(`load-voltage`),2) as 'Mittlere Lastausgang-Spannung (V)',
				ROUND(AVG(`load-current`),2) as 'Mittlerer Lastausgangs-Strom (A)',
				ROUND(AVG(`load-power`),2) as 'Mittlere Lastausgangs-Leistung (W)'	
				from epsolar_log.tbl_reading
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				GROUP BY date_format( `timestamp`, '%Y%m%d%H' )
				ORDER BY `timestamp` DESC";			
			break;
		case "month":
			$filename = "solar-export_month_" . date('Y-m-d') . ".csv";
			$mintimestamp=date("Y-m-d", strtotime('first day of this month')); 
			$maxtimestamp=date("Y-m-d", strtotime('last day of this month'));
			$statement="SELECT
				LEFT(`timestamp`, CHAR_LENGTH(`timestamp`) -8) as 'Tag des Monats',
				ROUND(AVG(`con-temp-main`),2) as 'Mittlere Controller-Temperatur (°C)',
				ROUND(AVG(`con-temp-heatsink`),2) as 'Mittlere Controller-Kühler-Temperatur (°C)',
				ROUND(AVG(`pv-voltage`),2) as 'Mittlere Generator-Spannung (V)',
				ROUND(AVG(`pv-current`),2) as 'Mittlerer Generator-Strom (A)',
				ROUND(AVG(`pv-power`),2) as 'Mittlere Generator-Leistung (W)',
				ROUND(AVG(`bat-voltage`),2) as 'Mittlere Batterie-Spannung (V)',
				ROUND(AVG(`bat-current`),2) as 'Mittlerer Batterie-Strom (A)',
				ROUND(AVG(`bat-power`),2) as 'Mittlere Batterie-Leistung (W)',
				ROUND(AVG(`bat-perc`),2) as 'Mittlere Batterie-Ladestand (%)',
				ROUND(AVG(`bat-temp`),2) as 'Mittlere Batterie-Temperatur (°C)',
				ROUND(AVG(`load-voltage`),2) as 'Mittlere Lastausgang-Spannung (V)',
				ROUND(AVG(`load-current`),2) as 'Mittlerer Lastausgangs-Strom (A)',
				ROUND(AVG(`load-power`),2) as 'Mittlere Lastausgangs-Leistung (W)'	
				from epsolar_log.tbl_reading
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				GROUP BY date_format( `timestamp`, '%Y%m%d' )
				ORDER BY `timestamp` DESC";
			break;
		case "year":
			$mintimestamp=date("Y-m-d", strtotime(date('Y-01-01'))); 
			$maxtimestamp=date("Y-m-d", strtotime(date('Y-12-31')));
			$filename = "solar-export_year_" . date('Y-m-d') . ".csv";
			$statement="SELECT
				CONCAT(WEEK(`timestamp`), ' / ', YEAR(`timestamp`)) as 'Kalenderwoche',
				ROUND(AVG(`con-temp-main`),2) as 'Mittlere Controller-Temperatur (°C)',
				ROUND(AVG(`con-temp-heatsink`),2) as 'Mittlere Controller-Kühler-Temperatur (°C)',
				ROUND(AVG(`pv-voltage`),2) as 'Mittlere Generator-Spannung (V)',
				ROUND(AVG(`pv-current`),2) as 'Mittlerer Generator-Strom (A)',
				ROUND(AVG(`pv-power`),2) as 'Mittlere Generator-Leistung (W)',
				ROUND(AVG(`bat-voltage`),2) as 'Mittlere Batterie-Spannung (V)',
				ROUND(AVG(`bat-current`),2) as 'Mittlerer Batterie-Strom (A)',
				ROUND(AVG(`bat-power`),2) as 'Mittlere Batterie-Leistung (W)',
				ROUND(AVG(`bat-perc`),2) as 'Mittlerer Batterie-Ladestand (%)',
				ROUND(AVG(`bat-temp`),2) as 'Mittlere Batterie-Temperatur (°C)',
				ROUND(AVG(`load-voltage`),2) as 'Mittlere Lastausgangs-Spannung (V)',
				ROUND(AVG(`load-current`),2) as 'Mittlerer Lastausgangs-Strom (A)',
				ROUND(AVG(`load-power`),2) as 'Mittlere Lastausgangs-Leistung (W)'	
				from epsolar_log.tbl_reading
				WHERE `timestamp` BETWEEN '".$mintimestamp."' AND '".$maxtimestamp."'
				GROUP BY WEEK( `timestamp`)
				ORDER BY `timestamp` DESC";
			break;
		case "allraw":
			$filename = "solar-export_rawdata_" . date('Y-m-d') . ".csv";
			$statement="SELECT 
				`timestamp` as 'Zeitpunkt',
				`con-temp-main` as 'Controller-Temperatur (°C)',
				`con-temp-heatsink` as 'Controller-Kühler-Temperatur (°C)',
				`pv-voltage` as 'Generator-Spannung (V)',
				`pv-current` as 'Generator-Strom (A)',
				`pv-power` as 'Generator-Leistung (W)',
				`bat-voltage` as 'Batterie-Spannung (V)',
				`bat-current` as 'Batterie-Strom (A)',
				`bat-power` as 'Batterie-Leistung (W)',
				`bat-perc` as 'Batterie-Ladestand (%)',
				`bat-temp` as 'Batterie-Temperatur (°C)',
				`load-voltage` as 'Lastausgangs-Spannung (V)',
				`load-current` as 'Lastausgangs-Strom (A)',
				`load-power` as 'Lastausgangs-Leistung (W)'	
				from epsolar_log.tbl_reading				
				ORDER BY `timestamp` DESC";
			break;
	}


	//CSV-Export
	
	$mydbcon->csvquery($statement, $filename);

}

else {
	//No Data-Presentation-Mode selected...
	
}


?>
