<?php
echo("<html>");
echo("<head>");
echo("<link href='epsolar_main.css' rel='stylesheet'>");
echo("<meta http-equiv='refresh' content='30' >");
echo("</head>");
echo("<body>");
echo("
	<script>
	</script>
");
$command = escapeshellcmd('../json_get_current_readings.py');
$output = shell_exec($command);
//echo($output); //DEBUG

echo("<H3>Aktuelle Werte:</H3>");	


if ($output != NULL) {
	$solardata=json_decode($output);
	//var_dump($solardata);
	//var_export($solardata, false);
	$controllerdata=$solardata[0];
	$generatordata=$solardata[1];
	$batterydata=$solardata[2];
	$loaddata=$solardata[3];

	//Datum / Uhrzeit:
	$heute = date("d.m.Y H:i:s");
	echo("<div id='datum'>".$heute."</div>");
	
	echo("<div id='piktogramme'>");
	//Sun/Moon-Image
	if ($generatordata->{'pv-voltage'} > 20.5){
	//Genug Sonneneinstrahlung (> 20.5 V) >> Tag
		echo("<img src='./img/sun_small.png' class='s' alt='Tag' title='Tag' />");
	}
	else {
		//Nicht genug Sonneneinstrahlung (< 10 V) >> Nacht
		echo("<img src='./img/moon_small.png' class='s' alt='Nacht' title='Nacht' />");
	}
	
	//Battery-Percentage-Image
	$batpimage="";
	if ($batterydata->{'bat-perc'} > 90) {
			$batpimage="./img/battery-full.png";
	}
	else if ($batterydata->{'bat-perc'} > 65 && $batterydata->{'bat-perc'} <= 90) {
			$batpimage="./img/battery-75.png";
	}
	else if ($batterydata->{'bat-perc'} > 35 && $batterydata->{'bat-perc'} <= 65) {
			$batpimage="./img/battery-50.png";
	}
	else if ($batterydata->{'bat-perc'} <= 35) {
			$batpimage="./img/battery-25.png";
	}
	echo("<img src='".$batpimage."' class='s' alt='Ladestand: ".$batterydata->{'bat-perc'}." %' title='Ladestand: ".$batterydata->{'bat-perc'}." %' />");
	
	//Battery-Temperature-Image
	$battimage="";
	$tempoutofrange=false;
	if ($batterydata->{'bat-temp'} > 28) {
		    //Battery too hot
			$battimage="./img/battery-hot.png";
		    $tempoutofrange=true;
	}
	else if ($batterydata->{'bat-temp'} <= 10) {
			// Battery too cold
			$battimage="./img/battery-cool.png";
		    $tempoutofrange=true;
	}
	//Nothing if battery is normal
	if ($tempoutofrange){
		echo("<img src='".$battimage."' class='s' alt='Batterietemperatur: ".$batterydata->{'bat-temp'}." °C' title='Batterietemperatur: ".$batterydata->{'bat-temp'}." °C' />");
	}


	//Battery-Laden/Entladen
	$batchargestate=0; //holding
	$batchargestext="Ladestand wird gehalten."; //holding
	$batcimage="";

/*
 Todo: 
 I think this part could be done better, but i'm not sure how at the moment,
 The controller reports wattage for battery as high as pv-genereator even if battery is fully charged.
 to my understanding it must be as follows:

 if battery is not fully charged and pv is generating more power than needed for load, the battery must be charging
 if it is generating not enough power for the load, the battery must be discharging
 if battery is fully charged and pv is generating enough power for load, the battery must be at hold

*/

	if ($batterydata->{'bat-perc'} >= 98) {
		//Battery is already fully charged
		$batchargestate=0; //holding
//		echo("Battery is on hold."); //Debug
		$batchargestext="Ladestand wird gehalten."; //holding
		$batcimage="./img/battery-hold.png";
	
	}
	else {
		//Battery is not full yet
		$wattagetolerance=1.0; //Measurement-tolerance in watts for gap between pv-power and bat-load-power
		
		if (($generatordata->{'pv-power'}+$wattagetolerance) >= ($batterydata->{'bat-power'}+$loaddata->{'load-power'})) {
			//Battery is not fully charged, but enough power is available
  		    	$batchargestate=1; //charging
//			echo("Battery is charging."); //Debug
			$batcimage="./img/battery-charge.png";
			$batchargestext="Batterie wird geladen."; //holding
		}
		else {
			//PV-Generator generates not enough power for battery and load
			//Batterypower is consumed to power the load
			if ($batterydata->{'bat-perc'} >= 5/* % */) {
				//Battery has still a bit capacity
				$batchargestate=2; //discharging
//				echo("Battery is discharging."); //Debug
				$batcimage="./img/battery-discharge.png";
				$batchargestext="Batterie wird entladen."; //holding
			}
			else {
				//Battery is is nearly depleted, not enough power is available
  		    		$batchargestate=3; //nearly depleted
//				echo("Battery is low."); //Debug
				$batcimage="./img/battery-discharge.png";
				$batchargestext="Batterie wird entladen."; //holding
		
			}
		}

	}

	
	echo("<img src='".$batcimage."' class='s' alt='".$batchargestext."' title='".$batchargestext."' />");

	
	
	echo("</div>"); //Piktogramme ENDE
	
	//Daten-Tabelle	
	echo("
	<table border=1>
		<th>Controller</th>
		<th>PV-Generator</th>
		<th>Batterie</th>
		<th>Lastausgang</th>
	");

	echo("
		<tr>
			<td>Hersteller: ".$controllerdata->{'con-manufacturer'}."</td>
			<td>Leistung: ".$generatordata->{'pv-power'}." W</td>
			<td>Leistung: ".$batterydata->{'bat-power'}." W</td>
			<td>Leistung: ".$loaddata->{'load-power'}." W</td>
		</tr>

		<tr>
			<td>Model: ".$controllerdata->{'con-model'}."</td>
			<td>Spannung: ".$generatordata->{'pv-voltage'}." V</td>
			<td>Spannung: ".$batterydata->{'bat-voltage'}." V</td>
			<td>Spannung: ".$loaddata->{'load-voltage'}." V</td>
		</tr>

		<tr>
			<td>SW-Version: ".$controllerdata->{'con-version'}."</td>
			<td>Strom: ".$generatordata->{'pv-current'}." A</td>
			<td>Strom: ".$batterydata->{'bat-current'}." A</td>
			<td>Strom: ".$loaddata->{'load-current'}." A</td>
		</tr>

		<tr>
			<td>Temperatur-Controller: ".$controllerdata->{'con-temp-controller'}." °C</td>
			<td></td>
			<td>Temperatur: ".$batterydata->{'bat-temp'}." °C</td>
			<td></td>
		</tr>

		<tr>
			<td>Temperatur-Kühlkörper: ".$controllerdata->{'con-temp-heatsink'}." °C</td>
			<td></td>
			<td>Ladestand: ".$batterydata->{'bat-perc'}." %</td>
			<td></td>
		</tr>

	");

	echo("
	</table>
	
	");

}
else {
	echo("Keine Daten empfangen, die Seite wird in 3 Sekunden neu geladen...");
	echo("<script>setTimeout(function(){window.location.reload(true);}, 3000);</script>"); //(Hopefully) Reload page after 3 seconds
}
echo("</body>");
echo("</html>");
?>
