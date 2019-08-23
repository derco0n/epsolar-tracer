<?php
echo("<H1>EPSolar-Log</H1>\r\n");
echo("<H2>by derco0n<H2>");
echo("<a href=\"https://github.com/derco0n/epsolar-tracer\" target=new>https://github.com/derco0n/epsolar-tracer</a>");
echo("<br>");
// $command = escapeshellcmd('../info.py');
$command = escapeshellcmd('../json_get_current_readings.py');
$output = shell_exec($command);
echo $output;


?>
