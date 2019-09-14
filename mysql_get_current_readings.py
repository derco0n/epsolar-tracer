#!/usr/bin/env python
# -*- coding: iso-8859-15 -*-


# This reads severals values from the EPSolar-charging-controller via RS485-USB-Cable (correct kernel-driver-module needed)

from pyepsolartracer.client import EPsolarTracerClient
import pyepsolartracer.config
import pyepsolartracer.database_mysql

# configure the client logging
import logging
logging.basicConfig()
log = logging.getLogger()
log.setLevel(logging.INFO)

DEFAULTCONFIG = "/etc/epsolar/epsolarlog.conf"

# Main:

myconf = pyepsolartracer.config.Config(DEFAULTCONFIG)
if not myconf.hasallsqlvalues:
            print("Not all Database-value-definitions found. Please check your config-file. Aborting")
            exit(2)

# Print current config
myconf.printconfig()

mydb = pyepsolartracer.database_mysql.db_mysql(
            myconf.mysql.server,
            myconf.mysql.user,
            myconf.mysql.password,
            myconf.mysql.db
        )

client = EPsolarTracerClient()
client.connect()

data = []  # New List for Data

response = client.read_device_info()

data.append(
    {
        'con-manufacturer':repr(response.information[0]),
        'con-model':repr(response.information[1]),
        'con-version':repr(response.information[2]),
        'con-temp-controller':client.read_input("Temperature inside equipment").value,
        'con-temp-heatsink':client.read_input("Power components temperature").value
    }
)

data.append(
    {
        'pv-voltage':client.read_input("Charging equipment input voltage").value,
        'pv-current':client.read_input("Charging equipment input current").value,
        'pv-power':client.read_input("Charging equipment input power").value
    }
)

data.append(
    {
        'bat-voltage':client.read_input("Charging equipment output voltage").value,
        'bat-current':client.read_input("Charging equipment output current").value,
        'bat-power':client.read_input("Charging equipment output power").value,
        'bat-temp':client.read_input("Battery Temperature").value,
        'bat-perc':client.read_input("Battery SOC").value
    }
)

data.append(
    {
        'load-voltage':client.read_input("Discharging equipment output voltage").value,
        'load-current':client.read_input("Discharging equipment output current").value,
        'load-power':client.read_input("Discharging equipment output power").value
    }
)

client.close()

if data.__len__() == 0:
    print("No data from solar-controller received...")
    exit(3)

# Data has been received
try:

    mydb.establish_connection()

    #DEBUG
    #print(data[0].get('con-manufacturer'))

    statement = "INSERT INTO tbl_reading " \
              "(`con-temp-main`, " \
              "`con-temp-heatsink`, " \
              "`pv-voltage`, " \
              "`pv-current`, " \
              "`pv-power`, " \
              "`bat-voltage`, " \
              "`bat-current`, " \
              "`bat-power`, " \
              "`bat-temp`, " \
              "`bat-perc`, " \
              "`load-voltage`, " \
              "`load-current`, " \
              "`load-power`)" \
              " VALUES " \
              "("+str(data[0].get('con-temp-controller')) + ", " + \
                str(data[0].get('con-temp-heatsink')) + ", " + \
                str(data[1].get('pv-voltage')) + ", " + \
                str(data[1].get('pv-current')) + ", " + \
                str(data[1].get('pv-power')) + ", " + \
                str(data[2].get('bat-voltage')) + ", " + \
                str(data[2].get('bat-current')) + ", " + \
                str(data[2].get('bat-power')) + ", " + \
                str(data[2].get('bat-temp')) + ", " + \
                str(data[2].get('bat-perc')) + ", " + \
                str(data[3].get('load-voltage')) + ", " + \
                str(data[3].get('load-current')) + ", " + \
                str(data[3].get('load-power')) + \
              ")"
    # print(statement) # DEBUG

    cursor = mydb.execute_statement(statement)



except:
    print("Error while writing values to database!")
    exit(4)

finally:
    mydb.disconnect(cursor)

    print("Finished")
    exit(0)
