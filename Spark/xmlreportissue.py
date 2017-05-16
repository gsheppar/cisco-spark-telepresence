# -*- coding: utf-8 -*-
import urllib 
import urllib2 
import re 
import cookielib
import sys
import os


path=os.path.abspath(os.curdir)
issue = path + "/XML/codec_report_issue.xml"

#storing the Authentication token in a file in the OS vs. leaving in script
username=config.codec_username
password=config.codec_password

f = open(issue,'r')
string = f.read()

location=str(sys.argv[1])
url = 'http://' + location + '/putxml'

param_data = string

passman = urllib2.HTTPPasswordMgrWithDefaultRealm()
passman.add_password(None, url, username, password)
authhandler = urllib2.HTTPBasicAuthHandler(passman)
opener = urllib2.build_opener(authhandler)
opener.addheaders = [('Content-Type', 'text/xml')]
urllib2.install_opener(opener)
urllib2.urlopen(url, param_data)

