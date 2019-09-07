<?php
/*
PHP - MySQLi-Klasse
geschrieben von Dennis Marx

*/
class mysql 
	{
	const  ClassVersion=0.41; //Versionsnummer dieser Klasse
	const  LastChangeDate="07.09.2019"; //Datum der letzteAenderung

	private $server=""; //Host
	private $user=""; //Benutzername
	private $pass=""; //Passwort
	private $db=""; //Datenbank
	private $row; //Zeile
	private $result; //Ergebniss
	private $fieldcount; //Fehldanzahl
	private $rowcount; //Zeilenanzahl
	private $count=0; //Zaehler
	private $linkstate;
	
	
	//private $connid; //VerbindungsID
	
	
	private $mysqliconnection; //Verbindung mysqli-Objekt
	
	public function getSQLIObject(){
		
			return $this->mysqliconnection;
		
	}
	
	

	private function verbinden() //SQL-Verbindung herstellen
		{
		//$connection=mysql_connect($this->server, $this->user, $this->pass);
		
		/*
		//DEBUG!!!
		var_dump($this->server);
		var_dump($this->user);
		var_dump($this->pass);
		var_dump($this->db);
		*/
		
		
		$this->mysqliconnection=new mysqli($this->server, $this->user, $this->pass, $this->db);
		
		if ($this->mysqliconnection->connect_error){
			return 0; //Verbindungsfehler
			
		}
		else {
			return 1; //Verbindung OK
		}
		/*
		if ($connection)
			{
				
					return $connection;
				
					return -1;
				
			}
		else 	
			{
			return -1;
			}
			*/
		}
	
	private function setdb($db) //Datenbank auswaehlen
		{
			
		if	(!$this->mysqliconnection->select_db($db)){
			die("Fehler beim Selektieren der Datenbank: ".$db." -> ". mysqli_error($this->mysqliconnection) );//...Meldung ausgeben und abbrechen.
		}	
		else {
			$this->db=$this->mysqliconnection->real_escape_string($db);
		}
		/*
		if (!mysql_select_db($db, $this->connid)) //Datenbankauswaehlen und bei Fehlschlag...
				{
				die("Fehler beim Selektieren der Datenbank: ".$db." -> ". mysql_error() );//...Meldung ausgeben und abbrechen.
				}
		else
				{
				$this->db=$db;
				}
				*/
		}
	
	
	//Konstruktormethode
	//public function mysql($newserver, $newuser, $newpass, $newdb="")
	public function __construct($newserver, $newuser, $newpass, $newdb="", $utf8=true)
		{
		//Variablen setzen
		$this->server=$newserver;
		$this->user=$newuser;
		$this->pass=$newpass;
		
		/*
		//DEBUG!!!
		var_dump($newserver);
		var_dump($newuser);
		var_dump($newpass);
		var_dump($newdb);
		var_dump($utf8);
		*/
		
		//$this->connid=$this->verbinden(); //Verbindung herstellen
		if ($this->verbinden()==1) //Pruefen ob eine Verbindung hergestellt wurde.
			{
			//Falls eine Verbindung besteht...
			if ($newdb!="") //Pruefen ob eine Datenbank angegeben wurde
				{
					
				$this->setdb($newdb); //Diese waehlen
						
				}
				if ($utf8==true) {
					if ($this->mysqliconnection->set_charset("UTF8")){ //Zeichensatz auf UTF-8 stellen
						
							//Erfolg: Dinge Tun
						}
					}
			}
		
		else
			{
			//Es wurde keine Verbindung hergestellt.
			echo("<br>Fehler beim Herstellen der Verbindung. / Error:  ".$this->mysqliconnection->connect_error); //Fehlermeldung ausgeben und Abbrechen.
			//die("<br>Fehler beim Herstellen der Verbindung. ConnectionID = ".$this->connid." / Error:  ".mysqli_error() ); //Alte Methode
			//unset($this); 
			}
		}	
		
	//Destruktormethode
	public function __destruct() 
		{
		if ($this->mysqliconnection->ping()) {	
		//Verbindung besteht
		$this->mysqliconnection->close(); //Verbindung schließen
		}
			
			/*
		if ($this->connid!=-1)//Pruefen ob eine Verbindung hergestellt wurde.
			{
			if( gettype($this->connid) == "resource") 
				{
				mysql_close($this->connid); //Versuchen die SQL-Verbindung sauber zu beenden.
				}
			}
			*/
		}
	
	//Setzt ein SQL-Statement ab ohne eine Ergebnis zurueckzuleifern
	//##############################################################
	public function simpleQuery ($statement, $silent=false)
		{
		//$statement=$this->mysqliconnection->real_escape_string($statement);	
		if ($this->mysqliconnection->query($statement) === TRUE){
			//Erfolg
			return true;
		}
		else {
			//Wenn dabei ein Fehler auftritt...
			if ($silent==false)
			{
				echo("Fehler bei ausf&uuml;hren der Abfrage. -> " . $this->mysqliconnection->error);
			}
			return false;
		}
		/*
		$this->result=mysql_query($statement, $this->connid);
		if (!$this->result) //Wenn dabei ein Fehler auftritt...
			{
			if ($silent==false)
			{
				echo("Fehler bei ausf&uuml;hren der Abfrage. -> " . mysql_error());
			}
			return false;
			}
		else 
			{
			return true;
			}
		*/	
		}
		
	//Liefert den Namen einer Splate anhand ihres Index
	###################################################
	function mysqli_field_name($result, $field_offset)
	{
		$properties = mysqli_fetch_field_direct($result, $field_offset);
		return is_object($properties) ? $properties->name : null;
	}
	
	
	//Liefert eine Spalte einer Abfrage als Array zurueck.
	//#############################################################
	public function res_arr ($statement, $spaltenid=0 /*Wenn nichts anderes angegeben, die erste Spalte benutzen*/, $skipfirst=true /*Standardmaessig die erste Zeile (Spaltenkopf) überspringen*/)
	{
	if ($this->db=="")
			{
			echo("Fehler: Keine Datenbank gesetzt");
			}
		else
			{
			
			$myretarr=array();
			
			//$statement=$this->mysqliconnection->real_escape_string($statement);
			
			
			//error_log("Statement: ".serialize($statement),0);
			$result=$this->mysqliconnection->query($statement); //Abfrage ausfuehren
			//error_log("Result: ".serialize($result),0);
			
			
			if ($result==NULL) //Wenn dabei ein Fehler auftritt...
				{
				//echo("Fehler bei ausf&uuml;hren der Abfrage. -> " . mysql_error());
				return false;
				}
			
			$i=0;
			
			if ($skipfirst==false)
			{
			//Spaltenkopf eintragen
			
			//$myretarr[$i]=mysql_field_name($result, $spaltenid);
			$myretarr[$i]=$this->mysqli_field_name($result, $spaltenid);
			$i++;
			
			}
			
			//while ($this->row = mysql_fetch_array($result)) //Solange Daten kommen
			while ($this->row = $result->fetch_array(MYSQLI_NUM))
				{
				$myretarr[$i]=$this->row[$spaltenid];
				$i++;
				}
			//mysql_free_result($result); //Ergebniscache leeren
			$result->free(); //Ergebniscache leeren
			//error_log("Return: ".serialize($myretarr),0);
			return $myretarr;	//Array zurueckgeben
			}
	}	
	
	//##################################################
		
	
	public function getCurrentDB() //Gibt die aktuelle Datenbank zurueck
		{	
		if ($this->db != "")
			{
			return $this->db;
			}
		else 
			{
			return "Der Klasse wurde keine Datenbank &uuml;bergeben.";
			}
		}
		
		
	
	//##################################################
	public function getVersion() //Version zurueckgeben
		{
		return self::ClassVersion;
		}


	//##################################################
	public function getLastChange() //Datum der letzten Aenderung zurueckgeben
                {
                return self::LastChangeDate;
                }


	//##################################################
	public function keepalive() //Verbindung am Leben halten.
		{
		//return mysql_ping($this->connid);
		return $this->mysqliconnection->ping();
		}
	
	//##################################################
	
	/*
	public function currentdb() //Aktuelle Datenbank zurueckgeben
		{
		if ($this->db != "")
			{
			return $this->db;
			}
		else 
			{
			return "Der Klasse wurde keine Datenbank &uuml;bergeben.";
			}
		}
		*/
	//##################################################
	public function genCustomInput($sqlstatement="") //Formular mit Textfeld zum absetzen eigener Statements generieren
		{
			
		$sqlstatement=$this->mysqliconnection->real_escape_string($sqlstatement);		
		
		echo("
		<p>
		<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
		<input name=\"sqlstatement\" type=\"text\" value=\"".$sqlstatement."\" size=\"80\" maxlength=\"8192\">
		<input type=\"submit\" value=\"Los!\"/>
		</form>
		</p>");

		if ($sqlstatement!="") //Wenn bereits ein Statement mitgegeben wurde
			{
			$this->tablequery($sqlstatement);
			}
		}

		
	//##################################################
	//public function getconnection()
		//{
		//return $this->connid; //VerbindungsID ausgeben
		//}
		
	//##################################################
	public function closeconnection()
		{
		return $this->mysqliconnection->close(); //Verbindung schließen und Status zurückgeben
		}
	
	//##################################################
	public function gettables()
		{
		if ($this->db=="")
			{
			echo("Fehler: Keine Datenbank gesetzt");
			}
		else
			{
			//$this->result=mysql_query("show tables", $this->connid); //Abfrage ausfuehren
			$this->result=$this->mysqliconnection->query("show tables"); //Abfrage ausfuehren
			if (!$this->result) //Wenn dabei ein Fehler auftritt...
				{
				echo("Fehler bei ausf&uuml;hren der Abfrage. -> " . $this->mysqliconnection->error);
				exit;
				}
			
			$i=0;
			while ($this->row = $this->result->fetch_array(MYSQLI_NUM))
			//while ($this->row = mysql_fetch_array($this->result)) //Solange Daten kommen
				{
				$tables[$i]=$this->row[0];
				$i++;
				}
			$this->result->free();
			return $tables;
			}
		}
		
	//##################################################
	public function printtables()
		{
		if ($this->db=="")
			{
			echo("Fehler: Keine Datenbank gesetzt");
			}
		else
			{
			$this->tablequery("show tables");
			}
		}

		
		
		
		
	public function jsonarrayquery($statement)
		{
			//Returns the result as a json_encoded_String
		//$this->result=mysql_query($statement, $this->connid) 
		
		//$statement=$this->mysqliconnection->real_escape_string($statement);
		
		$result=$this->mysqliconnection->query($statement)
			or die ("MySQL-Error: " . $this->mysqliconnection->error); //Abfrage ausfuehren
		
		//error_log("DEBUG: ".$result,0);
		
		
		//var_dump($result); //DEBUG
			try {
		$resrows=array();
		
			
			
			while ($r = $result->fetch_array(MYSQLI_ASSOC)) {
			$resrows[] = $r;
			}
					
		$myreturn=json_encode($resrows);
	
		
		return $myreturn;
		}
		catch(Exception $e)
		{
			return $result; //Result zurückgeben falls es sich nicht
		}
		}
		
	

	
	//##################################################	
	//tablequery(String SQL-Statement, [Int Tabellenrahmen=1,] [Int Spalte_mit_zu_kovertierendem_Datum=-1,] [Bool Zeilen/Spaltenanzahl ausgeben=True,] [String Feldfarbe1="#DADADA",] [String Feldfarbe2="#AAAAAA",] [String Spaltenkopffarbe="#999999"])
	public function tablequery($statement, $border=1, $dateconvcol=-1, $nullwert="NULL", $colrow=TRUE, $colcolor1="#DADADA", $colcolor2="#AAAAAA", $headcolor="#999999") //SQL-Abfrage - Ausgabe dynamisch als Tabelle
		{
			
			//echo("DB: ".$this->db."<br>");
			
			
			//$statement=$this->mysqliconnection->real_escape_string($statement);
			
			//echo("Query: ".$statement."<br>");
			
			$colswitch=1;

			
			
			
			//$result=mysql_query($statement, $this->connid); //Abfrage ausfuehren
			$result=$this->mysqliconnection->query($statement); //Abfrage ausfuehren
		
			
			
			if ($result==NULL) //Wenn dabei ein Fehler auftritt...
				{
				echo("Fehler bei ausf&uuml;hren der Abfrage. -> " . $this->mysqliconnection->error);
				exit;
				}
				
				/*
			echo("Result: "); //DEBUG
			var_dump($result); //DEBUG	
			echo("<br>");

				*/			
			//$fieldcount=mysql_num_fields($result); //Spaltenanzahl setzen (1.Spalte=1, 2.Spaltte=2...)
			$fieldcount=$this->mysqliconnection->field_count;
		    //$rowcount=mysql_num_rows($result);
			
			/*
			echo("Fieldcount: "); //DEBUG
			var_dump($fieldcount); //DEBUG
			echo("<br>");
			*/
			
			$rowcount=$result->num_rows;
			
			/*
			echo("Rowcount: "); //DEBUG
			var_dump($rowcount); //DEBUG
			echo("<br>");	
			*/
			
			if ($rowcount==0)
				{
				echo("\n<br>Die Abfrage lieferte keinen Datensatz.");
				//exit;
				return 0;
				}
				
			else
			
			{
		
			if ($colrow==TRUE)
				{
				//Spaltenanzahl ausgeben
				echo($fieldcount." Spalten / "); //Anzahl (1, 2, 3, ...)
		
				//Zeilenanzahl ausgeben
				echo($rowcount." Zeilen<br>");
				}
			
			$count=0;  //Spaltencounter setzen
		
			//Tabelle generieren
			echo("\n<table border=".$border.">");
			echo("\n<tr bgcolor=\"".$headcolor."\">");
		
			//Spaltenkoepfe generieren
			while ($count <= $fieldcount-1) //Feldnamen ermitteln und ausgeben -> Feldnummer Feld1=0, Feld2=1, Feld3=2, ...
			
				{
					
				echo("\n\t<td>".$this->mysqli_field_name($result, $count)."</td>");
				$count++; //Counter erhoehen
				}
			
			
			//Spalten generieren		
			//while ($this->row = mysql_fetch_array($result)) //Solange Daten kommen
			while ($row =$result->fetch_array(MYSQLI_NUM)) //Solange Daten kommen
			{
			$count=0; //Spaltencounter setzen
			if ($colswitch==1)
				{echo("\n<tr bgcolor=\"".$colcolor1."\">");} //neue Tabellenzeile mit Farbe 1
			else if ($colswitch==2)
				{echo("\n<tr bgcolor=\"".$colcolor2."\">");} //neue Tabellenzeile mit Farbe 2

				while ($count <= $fieldcount-1) //Solange in der Zeile Daten sind
					{
					if ($row[$count]) //Wenn kein NULL-Wert
						{
						if ($dateconvcol>-1 && $dateconvcol==$count) //Wenn eine Spalte fuer die Datumskonvertierung angegeben wurde und wir uns aktuell in der benannten Spalte befinden
							{
														
							if ($this->isSQLDate( $row[$count] ) ) //Wenn in diesem Feld ein SQL-Datumswert steht
								{
								echo("\n\t<td>".$this->conv_sqldate_to_date($row[$count])."</td>");	//Datumsfeld konvertieren und schreiben
								}
							else
								{
								echo("\n\t<td>".$row[$count]."</td>");	//Tabellendatenfeld schreiben		
								}
								
							}
						else
							{
							echo("\n\t<td>".$row[$count]."</td>");	//Tabellendatenfeld schreiben		
							}
						}
					else 
						{
						echo("\n\t<td>".$nullwert."</td>"); //Sonst "NULL" ausgeben
						}
					$count++; //Spaltenzaehler erhoehen
					}
				echo("\n</tr>\n"); //Tabellenzeile schliessen

			//Zeilenfarbe wechseln:
			if ($colswitch==1)
				{$colswitch=2;}
			else if ($colswitch==2)
				{$colswitch=1;}
			}
		
			//Tabelle schliessen
			//echo("</tr>\n");
			echo("\n</table>");
		
			//mysql_free_result($result); //Ergebniscache leeren
			$result->free();
			//Variablen zuruecksetzen
			/*
			$rows=$fieldcount;
			
			$fieldcount=NULL;
			$result=NULL;
			$this->row=NULL;
			return $rows;
			*/
			return $fieldcount;
			}
	}
	/*

	//##################################################	
	public function listquery($statement, $trennzeichen=";", $nullwert="NULL", $colrow=TRUE) //SQL-Abfrage - Ausgabe dynamisch als Liste
		{
		//$statement=$this->mysqliconnection->real_escape_string($statement);	
			
		$this->result=mysql_query($statement, $this->connid); //Abfrage ausfuehren
		
		if (!$this->result) //Wenn dabei ein Fehler auftritt...
			{
			echo("Fehler bei ausf&uuml;hren der Abfrage. -> " . mysql_error());
			exit;
			}
				
		$this->fieldcount=mysql_num_fields($this->result); //Spaltenanzahl setzen (1.Spalte=1, 2.Spaltte=2...)
		if ($colrow==TRUE)
			{
			//Spaltenanzahl ausgeben
			echo($this->fieldcount." Spalten / "); //Anzahl (1, 2, 3, ...)
		
			//Zeilenanzahl ausgeben
			echo(mysql_num_rows($this->result)." Zeilen<br>");
			}
			
		$this->count=0;  //Spaltencounter setzen
		
		//Spaltenkoepfe generieren
		while ($this->count <= $this->fieldcount-1) //Feldnamen ermitteln und ausgeben -> Feldnummer Feld1=0, Feld2=1, Feld3=2, ...
			{
			echo(mysql_field_name($this->result, $this->count).$trennzeichen);
			$this->count++; //Counter erhoehen
			}
			
		//Spalten generieren		
		while ($this->row = mysql_fetch_array($this->result)) //Solange Daten kommen
		{
		$this->count=0; //Spaltencounter setzen
		
			echo("<br>"); //neue Zeile
			while ($this->count <= $this->fieldcount-1) //Solange in der Zeile Daten sind
				{
				if ($this->row[$this->count]) //Wenn kein NULL-Wert
					{
					echo($this->row[$this->count].$trennzeichen);	//Tabellendatenfeld schreiben		
					}
				else 
					{
					echo($nullwert.$trennzeichen); //Sonst "NULL" ausgeben
					}
				$this->count++; //Spaltenzaehler erhoehen
				}
			
		}
		
		mysql_free_result($this->result); //Ergebniscache leeren
		//Variablen zuruecksetzen
		$this->fieldcount=NULL;
		$this->result=NULL;
		$this->row=NULL;
		}*/
	
	
	private function isSchaltjahr($jahr)
	{
	//Ermittelt ob ein Jahr ein Schaltjahr ist.
		if(($jahr % 400) == 0 || (($jahr % 4) == 0 && ($jahr % 100) != 0))
			{
			return TRUE;
			}
		else
			{
			return FALSE;
			}
	} 
	
	private function valiDATE($tag, $monat, $jahr)
		{
		//Die einzelnen Elemente pruefen
					if ($tag<1 || $tag > 31)
						{return false;}
					
					if ($monat<1 || $monat > 12)
						{return false;}
					
					if ($jahr<1000 || $jahr > 9999)
						{return false;}	
					
					//Bis hier hin passt der grobe Rahmen
				
					//Hier kommen nun noch genauere Pruefungen.
									
					if ($this->isSchaltjahr($jahr)) //Pruefen ob es sich bei dem Jahr um ein Schaltjahr handelt
						{
						if ($monat==2 && $tag > 29) //Wenn ja, darf der Februar 29 Tage haben
							{return false;}
						}
					else
						{
						if ($monat==2 && $tag > 28) //Wenn es sich nicht um ein Schaltjahr handelt, darf der Februar nur 28 Tage haben
							{return false;}
						}
				
					//Für die Monate April, juni, September und November die gueltigen Tage auf 30 begrenzen.
					if ($monat==2 && $tag > 29)
						{return false;}
					if ($monat==4 && $tag > 30)
						{return false;}	
					if ($monat==6 && $tag > 30)
						{return false;}	
					if ($monat==9 && $tag > 30)
						{return false;}	
					if ($monat==11 && $tag > 30)
						{return false;}		
				
					//Alles OK. Es muss sich nun um ein gueltiges Datum handeln.
					return true;
		}
	
	
	public function isDate ($datstr /*Erwartet ein Datum im Format TT.MM.JJJJ*/) 
		{
		//Prueft ob es sich um ein Datum handelt.
		if (strlen($datstr) != 10)  //Wenn der String keine 10 Zeichen lang ist.
			{return false;}
		else
			{
			$datarr=str_split($datstr); //Den String in ein Array konvertieren
			if ($datarr[2] != '.' || $datarr[5] != '.') //Wenn an der dritten und 5. Stelle (beginnend bei 0) kein Punkt ist...
			{return false;}
		
			else
				{
				$dotcount=0;
				foreach ($datarr as $char) //das Stringarray Zeichen  fuer Zeichen durchgehen.
					{
					if ($char=='.') //Wenn aktuelles Zeichen ein Punkt ist
						{
						$dotcount++; //...den Punktezaehler erhoehen
						}
				
					}
			
			
				if ($dotcount != 2) //Wenn im String nicht genau 2 Punkte vorhanden sind...
					{return false;}
				else	
					{
				
					$dateparts=explode(".", $datstr); //Den Datumstring an den Punkten aufsplitten
					foreach ($dateparts as $part) //Jeden Teil des Datumstrings durchgehen
						{
						if (!is_numeric($part)) //Sobald ein Element nicht numerisch ist, false zurueckgeben
							{return false;}
						}
					//Die Einzelnen Teile in Integer wandeln
					$tag=intval($dateparts[0]);
					$monat=intval($dateparts[1]);
					$jahr=intval($dateparts[2]);
				
				
					//Das Datum pruefen
					
					if (!$this->valiDATE($tag, $monat, $jahr))
						{return false;}
					else
						{return true;}
						
					
				
					}
			
				}
			}
	
		}
	
	
	public function isSQLDate ($datstr /*Erwartet ein Datum im Format JJJJ-MM-TT*/) 
	{
	//Prueft ob es sich um ein Datum handelt.
	if (strlen($datstr) != 10)  //Wenn der String keine 10 Zeichen lang ist.
		{return false;}
	else
		{
		
		
		
		$datarr=str_split($datstr); //Den String in ein Array konvertieren
		if ($datarr[4] != '-' || $datarr[7] != '-') //Wenn an der 5. und 8. Stelle (beginnend bei 0) kein Minus ist...
			{return false;}
		
		else
			{
			$strichcount=0;
			foreach ($datarr as $char) //das Stringarray Zeichen  fuer Zeichen durchgehen.
				{
				if ($char=='-') //Wenn aktuelles Zeichen ein Punkt ist
					{
					$strichcount++; //...den Strichzaehler erhoehen
					}
				
				}
			
			if ($strichcount != 2) //Wenn im String nicht genau 2 Striche vorhanden sind...
				{return false;}
			else	
				{
				
				$dateparts=explode("-", $datstr); //Den Datumstring an den Strichen aufsplitten
				foreach ($dateparts as $part) //Jeden Teil des Datumstrings durchgehen
					{
					if (!is_numeric($part)) //Sobald ein Element nicht numerisch ist, false zurueckgeben
						{return false;}
					}
				//Die Einzelnen Teile in Integer wandeln
				$tag=intval($dateparts[2]);
				$monat=intval($dateparts[1]);
				$jahr=intval($dateparts[0]);
				
				//Das Datum pruefen
				if (!$this->valiDATE($tag, $monat, $jahr))
						{return false;}
					else
						{return true;}
				
				}
			
			}
		}	
	
	}
	
	
	//##################################################
	public function conv_date_to_sqldate($datum, $sqlkonform=false)
		{
		//Macht ein Datum (TT.MM.JJJJ) SQL-Konform (JJJJ-MM-TT)
		if (!$this->isDate($datum))
			{exit;}
		else	
			{
			$dateparts=explode(".", $datum);
			
			if ($sqlkonform=false)
				{
				$retstr=$dateparts[2] . "-" . $dateparts[1] . "-" . $dateparts[0]; //Nicht SQL-Konform Bsp.: 2014-08-06
				}
			else	
				{
				$retstr=$dateparts[2] . $dateparts[1] . $dateparts[0]; //SQL-Konform Bsp.: 20140806
				}
			
			return $retstr;
			}
		
		}
	
	
	//##################################################
	public function conv_sqldate_to_date($datum)
		{
		//Macht ein SQL-Datum (JJJJ-MM-TT) zu einem normalen Datum (TT.MM.JJJJ) 
		if (!$this->isSQLDate($datum))
			{exit;}
		else	
			{
			$dateparts=explode("-", $datum);
			
			$retstr=$dateparts[2] . "." . $dateparts[1] . "." . $dateparts[0];
			
			return $retstr;
			}
		
		}
	/*
	
	//##################################################
	public function array_result($sql = NULL, &$row = '') 
    		{ 
        	$inc = ''; 
        	//if($sql === NULL) //Drei Gleichzeichen?? Tippfehler?
		if($sql == NULL)
        		{ 
            		$inc = $this->last_injection; 
            		} 
		else 	{ 
            		$inc = $sql; 
        		} 
         
        	$row = mysql_fetch_array($inc); 
         
        	return($row); 
    		}
		*/
		
	//##################################################	
	
	/*
	public function genDropdownByTable ($table, $limitrows=200, $limitfields=1000, $beginfield=0, $datetimefield=-1)
		{
		if ($this->db=="")
			{
			echo("Fehler: Keine Datenbank gesetzt");
			}
		else
			{
				
				$table=$this->mysqliconnection->real_escape_string($table);
			
			$query="select * from ".$table;
			
			if ($myresult=mysql_query($query, $this->connid))
				{
				//echo("<H2>Abfrage der letzten ".$limitrows." Datens&auml;tze.</H2>");
				//DEBUG:
				//echo("Das Resultset ist nicht leer.");
				//DEBUG ENDE
				$this->count=0;
				
				//Tabellenkopf:
				echo("<table>\n");
				echo("<tr>\n");
				$this->fieldcount=mysql_num_fields($myresult); //Spaltenanzahl setzen (1.Spalte=1, 2.Spalte=2...)
				while ($this->count <= $this->fieldcount-1 && $this->count < $limitfields) //Feldnamen ermitteln
					{
					
					//DEBUG:
					//echo("".mysql_field_name($myresult, $this->count)."; ");
					//DEBUG ENDE
					
					$fieldarray[$this->count]=mysql_field_name($myresult, $this->count);
					
					if ($this->count >= $beginfield)
						{
						echo("\t<td>".$fieldarray[$this->count]."</td>\n");
						}
						
					$this->count++; //Counter erhoehen
					}
				echo("</tr>\n");	
				$myfieldcount=$this->count; //Feldanzahl sichern
					
				$this->count=0; //Zähler zurücksetzen
				
				echo("<tr>\n");
				
				while ($this->count <= $myfieldcount && $this->count < $limitfields) //Einträge holen und array schreiben
					{
					
					//echo("<td>".$fieldarray[$this->count].":</td>");
					if ($this->count==$datetimefield)
						{
						//Abfragen zur für Datetime-Feld
						if ($limitrows<0) //Wenn Zweilenlimit < 0
							{
							//Ohne Limit
							$query="SELECT DATE(".$fieldarray[$this->count].") FROM ".$table." GROUP BY DATE(".$fieldarray[$this->count].") ORDER BY ".$fieldarray[$this->count]." DESC";
							}
						else //Wenn gueltiges Zeilenlimit angegeben
							{
							//Mit Limit
							$query="SELECT DATE(".$fieldarray[$this->count].") FROM ".$table." GROUP BY DATE(".$fieldarray[$this->count].") ORDER BY ".$fieldarray[$this->count]." DESC LIMIT ".$limitrows."";
							}
						}
						
					else //Wenn dies nicht das Datetimefeld ist
						{
						//Standardabfragen
						if ($limitrows<0) //Wenn Zwilenlimit < 0
							{
							//Ohne Limit
							$query="SELECT ".$fieldarray[$this->count]." FROM ".$table." GROUP BY ".$fieldarray[$this->count]." ORDER BY ".$fieldarray[$this->count]." DESC";
							}
						else //Wenn gueltiges Zeilenlimit angegeben
							{
							//Mit Limit
							$query="SELECT ".$fieldarray[$this->count]." FROM ".$table." GROUP BY ".$fieldarray[$this->count]." ORDER BY ".$fieldarray[$this->count]." DESC LIMIT ".$limitrows."";
							}
						
						}
						
						//DEBUG Query ausgeben
						//echo("<br>->".$query."<br>");
						//break;
						//DEBUG ENDE
						
					$tempres=mysql_query($query, $this->connid); //Abfrage mit dem querystring durchfuehren
						
						
					$rowcount=0;
					while($row = $this->array_result($tempres)) //Array durchgehen und ...
						{ 
						$rowcount++;
						if ($this->count==$datetimefield)
							{
							$select[$rowcount]=$row["DATE(".$fieldarray[$this->count].")"];
							}
						else
							{
							$select[$rowcount]=$row[$fieldarray[$this->count]];
							}
						}
					
					

					//...Auswahlfeld generieren
					if ($this->count >= $beginfield)
						{
						echo ("\t<td>\n");							
						echo ("\t<select name=\"".$fieldarray[$this->count]."\" size=\"1\">\n");
						echo ("\t<option value=\"%\">%</option>\n");	
						
							
						while ($rowcount>0)
							{
							echo ("\t<option value=\"".$select[$rowcount]."\"");
	  
							if (isset($_POST["\"".$fieldarray[$this->count]."\""]))
								{
								if(strcmp($_POST["\"".$fieldarray[$this->count]."\""],$select[$rowcount])==0) 
									{
									echo("selected"); //Default Wert setzen, wenn verfuegbar
									} 
								}
							if ($this->count >= $beginfield)
								{	
								echo(">".$select[$rowcount]."</option>\n");
								}
							
							$rowcount--;
							}
						echo("\t</select>\n");
						echo("\t</td>\n\n");
						}
					
					$this->count++;
					//
					}
				echo("</tr>\n");
				//Tabellenende	
				echo("</table>\n");	
					
				}
			else 
				{
				echo("Fehler: Die angegebene Tabelle existiert nicht.");
				}
				
			mysql_free_result($tempres); //Ergebniscache leeren
			//Variablen zuruecksetzen
			$this->fieldcount=NULL;
			$this->result=NULL;
			$this->row=NULL;
			}
		}
	
		*/
	}

?>
