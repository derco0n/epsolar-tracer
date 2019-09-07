# Used for writing log entries
GRANT INSERT on epsolar_log.tbl_reading TO 'epsolarlogwriter'@'127.0.0.1' IDENTIFIED BY 'SECRETPASSWORD'; 

# used for querying logs via webinterface
GRANT SELECT on epsolar_log.tbl_reading TO 'epsolarlogreader'@'127.0.0.1' IDENTIFIED BY 'OTHERSECRETPASSWORD'; 
