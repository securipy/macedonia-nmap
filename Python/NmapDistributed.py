#!/usr/bin/env python
# -*- encoding: utf-8 -*-

""" Nmap Distributed """

import sys
import re
import os
import smtplib
import time,datetime
import argparse
import requests
import jwt
import json
import nmap
from time import gmtime, strftime
import os


__author__			= "GoldraK"
__credits__			= "GoldraK"
__version__			= "0.1"
__maintainer__		= "GoldraK"
__email__			= "goldrak@gmail.com"
__status__			= "Development"


class NmapDistributed():

	def __init__(self):
		self.version = "0.1"
		self.log = True
		self.verbose = True
		self.public_key = "1584344601587bdb9a4db022.75409245"
		self.private_key = "1331909997587bdb9a4db479.67780769"
		self.app_granada_token = "app-granada"
		self.domain = "http://api.granada.com"
		self.nm = nmap.PortScanner()


	def NmapDistributed(self):
		args = self.__handleArguments()
		token = self.__createJWT()
		headers = {self.app_granada_token:token}
		r = requests.get(self.domain+'/device/nmap/distributed',headers=headers)
		if r.status_code == 200:
			data = json.loads(r.text)
			if(data['response'] == True):
				if not data['result']:
					if self.log:
						self.__writeLog("Not devices to scan")
				else:
					for toscan in data['result']:
						self.__scanDevice(toscan['ip_domain'],toscan['id'])
			else:
				self.__writeLog(data['message'])
		else:
			self.__writeLog(r.text)
			 

	def __scanDevice(self,ip,id_scan):
		msg = "Start scan device: "+ip
		self.__writeLogConsole(msg)
		try:
			self.nm.scan(hosts=ip,arguments="-sV -sC")
		except Exception as e:
			raise e
			self.__writeLog(e)
		for host in self.nm.all_hosts():
		    for proto in nm[host].all_protocols():
		        lport = nm[host][proto].keys()
		        lport = sorted(lport)
		        for port in lport:
		        	r = requests.post(self.domain+'device/nmap/port', data = {'id_scan':id_scan,'port':port,'protocol':nm[host][proto],'state':nm[host][proto][port]['state'],'service':nm[host][proto][port]['product'],'version':nm[host][proto][port]['version'],'banner':nm[host][proto][port]['extrainfo']},headers=headers)
		        	if r.status_code != 200:
	   					data = json.loads(r.text)
	   					if(data['response'] == False):
	   						self.__writeLog(data['message'])
	
				

	def __createJWT(self):
		return jwt.encode({'public_key':self.public_key,'module':'Nmap'},self.private_key,algorithm='HS256')


	
	def __handleArguments(self,argv=None):
		"""
		This function parses the command line parameters and arguments
		"""
		parser = argparse.ArgumentParser(description='Nmap distributed')
		parser.add_argument('--verbose',action='store_true',help='verbose flag')
		parser.add_argument('--log',action='store_true',help='Log flag')
		args = parser.parse_args()
		
		return args


	def __sendEmail(self,alert_mac,opts):
		"""
		This function send mail with the report
		"""
		header  = 'From: %s\n' % opts.user
		header += 'To: %s\n' % opts.emailto
		if alert_mac:
			header += 'Subject: New machines connected\n\n'
			message = header + 'List macs: \n '+str(alert_mac)
		else:
			header += 'Subject: No intruders - All machines known \n\n'
			message = header + 'No intruders'

		server = smtplib.SMTP(opts.server+":"+opts.port)
		server.starttls()
		server.login(opts.user,opts.password)
		if self.verbose or self.log:
			debugemail = server.set_debuglevel(1)
			if self.verbose:
				self.__consoleMessage(debugemail)
		problems = server.sendmail(opts.user, opts.emailto, message)
		print (problems)
		server.quit()


	def __consoleMessage(self,message):
		"""
		This function write console message
		"""
		ts = time.time()
		st = datetime.datetime.fromtimestamp(ts).strftime('%Y-%m-%d %H:%M:%S')
		print ('['+st+'] '+str(message))


	def __writeLog(self,log):
		"""
		This function write log
		"""
		ts = time.time()
		st = datetime.datetime.fromtimestamp(ts).strftime('%Y-%m-%d %H:%M:%S')
		if os.path.isfile('log.txt'):
			try:
				file_read = open('log.txt', 'a')
				file_read.write('['+st+'] '+log+"\n")
				file_read.close()
			except IOError:
				msg = 'ERROR: Cannot open log.txt'
				if self.verbose:
					self.__consoleMessage(msg)
				sys.exit(-1)
		else:
			msg = "ERROR: The log file  doesn't exist!"
			if self.verbose:
				self.__consoleMessage(msg)
			sys.exit(-1)

	def __writeLogConsole(self,msg):
		if self.log:
			self.__writeLog(msg)
		if self.verbose:
			self.__consoleMessage(msg)


if __name__ == "__main__":
	p = NmapDistributed()
	p.NmapDistributed()
