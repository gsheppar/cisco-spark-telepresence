#!/usr/bin/python
import pyCiscoSpark
import sys
import config

#storing the Authentication token in a file in the OS vs. leaving in script
accesstoken=config.spark_accesstoken

#grabbing messageId from the command line argument from ciscospark.php
msgid=str(sys.argv[1])

#reading text from message that caused alert
txt=pyCiscoSpark.get_message(accesstoken,msgid)
print txt['text']
#return
