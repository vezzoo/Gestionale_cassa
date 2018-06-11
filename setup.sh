#!/bin/bash

mkdir -p ./sap/ui5

wget https://openui5.hana.ondemand.com/downloads/openui5-runtime-1.54.4.zip
unzip openui5-runtime-1.54.4.zip

mv ./resources/* ./sap/ui5/