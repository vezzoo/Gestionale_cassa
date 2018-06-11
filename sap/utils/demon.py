#!/bin/python3

import os
import time

printer = "HP_HP_LaserJet_P2015_Series"
printer1 = "HP_LaserJet_400_M401dne"

while True:
	print("Chekcing files to print ... ")
	for file in os.listdir("./saved_bills/"):
		if file.endswith(".unprinted"):
			allaStampa = file.replace(".unprinted", "")
			os.rename("./saved_bills/" + allaStampa + ".unprinted","./saved_bills/" + allaStampa)
			print("Printing %s" % ("./saved_bills/" + allaStampa))
			os.system("lpr -P %s %s" % (printer, "./saved_bills/" + allaStampa))
			#os.system("lpr -P %s %s" % (printer1, "./saved_bills/" + allaStampa))
			time.sleep(2)
	time.sleep(2)
