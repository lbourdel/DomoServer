#!/usr/bin/env python
# -*- coding: utf-8 -*-

import requests
import json
import hmac
import hashlib

def connexion_post(method,data=None,headers={}):
	url = "http://mafreebox.freebox.fr/api/v4/"+method
	if data: data = json.dumps(data)
	return json.loads(requests.post(url, data=data, headers=headers).text)

def connexion_get(method, headers={}):
	url = "http://mafreebox.freebox.fr/api/v4/"+method
	return json.loads(requests.get(url, headers=headers).text)
	
def mksession():
	token = b'nK3U2oRc6C7JejudI1DNjC87sIw+mY3mFCVmZO+giZHGOciindwk6TK53P5ZCMvk'
	challenge=connexion_get("login/")["result"]["challenge"]
	data={
		"app_id": "fr.freebox.testapp",
		"password": hmac.new(token,challenge.encode('utf-8'),hashlib.sha1).hexdigest()
	}
	print(data['app_id'])
	print(data['password'])
	r = connexion_post("login/session/",data)["result"]["session_token"]

	return r

def recherche_app(session_token):
	method = "lan/browser/pub/"
	resultat =  connexion_get(method, headers={"X-Fbx-App-Auth": session_token})
	#recherche dans resultat des élements connectés avec le adresse MAC
	for val in resultat["result"]:
		print('name ==', val['default_name'])
		for  val2  in val["l2ident"].values():
			print(val2)
			# if val2 == "28:5A:EB:83:25:8C":
			# 	telephone = val["active"]
#	return telephone 


r = requests.get("http://mafreebox.freebox.fr/api/v4/login/")
#print( r.text)

challenge = json.loads(r.text)['result']['challenge']
password_salt = json.loads(r.text)['result']['password_salt']
#print(challenge)
#print(password_salt)

session_token = mksession() #création session API Freebox

textreturned = recherche_app(session_token)

print(textreturned)

