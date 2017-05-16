# -*- coding: utf-8 -*-
import urllib 
import urllib2 
import re 
import cookielib
import sys
import config
import os


path=os.path.abspath(os.curdir)
statusdiag = path + "/XML/status_diag.xml"

#storing the Authentication token in a file in the OS vs. leaving in script
username=config.codec_username
password=config.codec_password


location=str(sys.argv[1])
url = 'http://' + location + '/getxml?location=/Status/Diagnostics'


passman = urllib2.HTTPPasswordMgrWithDefaultRealm()
passman.add_password(None, url, username, password)
authhandler = urllib2.HTTPBasicAuthHandler(passman)
opener = urllib2.build_opener(authhandler)
opener.addheaders = [('Content-Type', 'text/xml')]
urllib2.install_opener(opener)
response = urllib2.urlopen(url)


message = response.read() 

g=open (statusdiag,"w")
g.write (message)
