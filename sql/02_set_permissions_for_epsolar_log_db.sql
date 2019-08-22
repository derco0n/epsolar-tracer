# Used for writing log entries
GRANT INSERT on epsolar_log.tbl_reading TO 'epsolarlogwriter'@'localhost' IDENTIFIED BY 'SECRETPASSWORD'; 

# used for querying logs via webinterface
GRANT SELECT on epsolar_log.tbl_reading TO 'epsolarlogreader'@'localhost' IDENTIFIED BY 'OTHERSECRETPASSWORD'; 
