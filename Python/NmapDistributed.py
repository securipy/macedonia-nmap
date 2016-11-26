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
from libnmap.process import NmapProcess
from libnmap.parser import NmapParser
from time import gmtime, strftime
import os


__author__ 		= "GoldraK"
__credits__ 	= "GoldraK"
__version__ 	= "0.1"
__maintainer__ 	= "GoldraK"
__email__ 		= "goldrak@gmail.com"
__status__ 		= "Development"


class NmapDistributed():

	def __init__(self):
		self.version = "0.1"
		self.log = True
		self.verbose = True
		self.public_key = ""
		self.private_key = ""
		self.ip_domain_server = ""
		self.app_granada_token = "app-granada"
		self.domain = ""



	def NmapDistributed(self):
		args = self.__handleArguments()
		token = self.__createJWT()
		headers = {self.app_granada_token:token}
		r = requests.get(self.domain+'/nmap/distributed',headers=headers)
		print r.text
		data = json.loads(r.text)
		print data
		if(data['response'] == True):
			if not data['result']:
				if self.log:
					self.__writeLog("Not devices to scan")
			else:
				for toscan in data['result']:
					self.__scanDevice(toscan['ip_domain'],toscan['id'])
		else:
			self.__writeLog(data['message'])

	def __scanDevice(self,ip,id_scan):
		msg = "Start scan device: "+ip
		self.__writeLogConsole(msg)
		nm = NmapProcess(str(ip),options="-sV -sC")
		rc = nm.run()
		if nm.rc == 0:
			try:
				file_name = str(ip)+"_"+strftime("%Y-%m-%d_%H:%M:%S", gmtime())+'.xml'
				file_read = open(str(ip)+"_"+strftime("%Y-%m-%d_%H:%M:%S", gmtime())+'.xml', 'w+')
				file_read.write(nm.stdout)
				file_read.close()
			except IOError:
				msg = "Error create output xml"
				self.__writeLogConsole(msg)
				sys.exit(-1)
			self.__uploadScan(file_name,id_scan)

		else:
		    self.__writeLog(nm.stderr)

	def __uploadScan(self,file_name,id_scan): 
		token = self.__createJWT()
		headers = {self.app_granada_token:token}
		files = {'nmap': open(os.path.dirname(os.path.realpath(__file__))+"/"+file_name, 'rb')}
		r = requests.post(self.domain+'/nmap/scan', data = {'id_scan':id_scan},files=files,headers=headers,stream=True)
		data = json.loads(r.text)
		if(data['response'] == True):
			msg = "Scan Upload, prepare to analyze"
			self.__writeLogConsole(msg)
			self.__parseScan(file_name,id_scan)
		else:
			self.__writeLog("Error upload data to server "+file_name)

	def __parseScan(self,file_name,id_scan):
		full_path = os.path.dirname(os.path.realpath(__file__))+"/"+file_name
		nmap_report = NmapParser.parse_fromfile(full_path)
		token = self.__createJWT()
		headers = {self.app_granada_token:token}
		for host in nmap_report.hosts:
			if len(host.hostnames):
				tmp_host = host.hostnames.pop()
			else:
				tmp_host = host.address

			#self.__writeLogConsole(host.services)
		
			hostport = {}
			hosttotal = {}
			
			for serv in host.services:
				r = requests.post(self.domain+'/nmap/port', data = {'id_scan':id_scan,'port':serv.port,'protocol':serv.protocol,'state':serv.state,'service':serv.service,'banner':serv.banner},headers=headers)
				self.__writeLogConsole(r.text)
				

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
		print problems
		server.quit()


	def __consoleMessage(self,message):
		"""
		This function write console message
		"""
		ts = time.time()
		st = datetime.datetime.fromtimestamp(ts).strftime('%Y-%m-%d %H:%M:%S')
		print '['+st+'] '+str(message)


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
