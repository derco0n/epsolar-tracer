# Config-component

# Reads a config from a file
# Configfile must look like this:
# [mysql]
# server=servername.fqdn.tld
# user=databaseuser
# pass=password
# database=database_to_use

import configparser
import pyepsolartracer.settings_mysql

class Config:
    def __init__(self, configfile="/etc/epsolar/epsolarlog.conf"):
        self.configfile=configfile
        self.mysql = pyepsolartracer.settings_mysql.settings_mysql()
        self.readconfig()

    def printconfig(self):
        # Prints out current values, which are read from configfile
        print("Config was read from: " + self.configfile)
        print("")
        print("MySQL-Server: " + self.mysql.server)
        print("MySQL-User: " + self.mysql.user)
        # print("MySQL-Password: "+ self.mysql.password)  # DEBUG (use with caution!)
        print("MySQL-DB: " + self.mysql.db)

    def readconfig(self):
        # Reads settings from a configfile specified in constructor...

        config = configparser.ConfigParser()
        config.read(self.configfile)
        self.hasallsqlvalues=True

        # Section mysql
        if 'mysql' in config:
            # Config contains section "[mysql]"
            try:
                self.mysql.server = config.get('mysql', 'server')
            except:
                print("Did not found value for server in "+self.configfile)
                self.hasallsqlvalues = False

            try:
                self.mysql.user = config.get('mysql', 'user')
            except:
                print("Did not found value for user in "+self.configfile)
                self.hasallsqlvalues = False

            try:
                self.mysql.password = config.get('mysql', 'pass')
            except:
                print("Did not found value for pass in " + self.configfile)
                self.hasallsqlvalues = False

            try:
                self.mysql.db = config.get('mysql', 'database')
            except:
                print("Did not found value for database in " + self.configfile)
                self.hasallsqlvalues = False


