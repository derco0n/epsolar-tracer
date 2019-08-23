#!/usr/bin/env python
# -*- coding: iso-8859-15 -*-

# This reads severals values from the EPSolar-charging-controller via RS485-USB-Cable (correct kernel-driver-module needed)

from pyepsolartracer.client import EPsolarTracerClient
from pyepsolartracer.registers import registers, coils

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

response = client.read_input("Charging equipment rated input voltage")
print(str(response))

for reg in registers:
    value = client.read_input(reg.name)
    print(value)
    #if value.value is not None:
    #    print(client.write_output(reg.name,value.value)

for reg in coils:
    value = client.read_input(reg.name)
    print(value)
    #print(client.write_output(reg.name,value.value)

client.close()
