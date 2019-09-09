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
	if ($generatordata->{'pv-voltage'} > 10.0){
	//Genug Sonneneinstrahlung (> 10 V) >> Tag
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
