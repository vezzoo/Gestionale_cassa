#!/bin/bash

ENSCRIPT="--no-header --margins=7:7:7:7 --font=Courier18 --word-wrap --media=A4"
export ENSCRIPT
cat $1 | iconv -c -f utf-8 | /usr/bin/enscript -p - | lpr -P $2
