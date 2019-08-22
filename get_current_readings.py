#!/usr/bin/env python
# -*- coding: iso-8859-15 -*-


# This reads severals values from the EPSolar-charging-controller via RS485-USB-Cable (correct kernel-driver-module needed)

from pyepsolartracer.client import EPsolarTracerClient

# configure the client logging
import logging
logging.basicConfig()
log = logging.getLogger()
log.setLevel(logging.INFO)


client = EPsolarTracerClient()
client.connect()

response = client.read_device_info()
print("Manufacturer:", repr(response.information[0]))
print("Model:", repr(response.information[1]))
print("Version:", repr(response.information[2]))

print("Aktuelle Werte:")
print("")
print("PV-Generator:")
print(client.read_input("Charging equipment input voltage"))  # Momentary Voltage of PV-Generator
print(client.read_input("Charging equipment input current"))  # Momentary Current of PV-Generator
print(client.read_input("Charging equipment input power"))  # Momentary Power of PV-Generator
print("")
print("Battery:")
print(client.read_input("Charging equipment output voltage"))  # Momentary Voltage of Battery-Output
print(client.read_input("Charging equipment output current"))  # Momentary Current of Battery-Output
print(client.read_input("Charging equipment output power"))  # Momentary Power of Battery-Output
print("")
print("LoAD::")
print(client.read_input("Discharging equipment output voltage"))  # Momentary Voltage of LOAD-Output
print(client.read_input("Discharging equipment output current"))  # Momentary Current of LOAD-Output
print(client.read_input("Discharging equipment output power"))  # Momentary Power of LOAD-Output
print("")
print("Temperatures:")
print(client.read_input("Battery Temperature"))  # Momentary Temperature of Battery
print(client.read_input("Temperature inside equipment"))  # Momentary Temperature of Controller
print(client.read_input("Power components temperature"))  # Momentary Temperature of Heatsinks
print("")
print("Battery:")
print(client.read_input("Battery SOC"))  # Momentary Percentage of Battery's remaining capacity

client.close()
