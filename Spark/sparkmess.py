#!/usr/bin/python
import pyCiscoSpark
import sys
import config
import os


path=os.path.abspath(os.curdir)
log = path + "/TXT/spark_send_message_log.txt"
message = path + "/TXT/spark_send_message.txt"

print log
print message

#storing the Authentication token and roomdID for Video BOT
accesstoken=config.spark_accesstoken
roomid=config.spark_roomid

f=open (log,"a")
g=open (message,"r")

newmessage = g.read()

f.write ("\n")
f.write (newmessage)

pyCiscoSpark.post_message(accesstoken,roomid,newmessage)
