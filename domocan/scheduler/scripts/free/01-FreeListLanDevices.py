#!/usr/bin/env python
# -*- coding: utf-8 -*-

import requests
import json
import hmac
import hashlib

def connexion_post(method,data=None,headers={}):
	url = "http://mafreebox.freebox.fr/api/v8/"+method
	if data: data = json.dumps(data)
	return json.loads(requests.post(url, data=data, headers=headers).text)

def connexion_get(method, headers={}):
	url = "http://mafreebox.freebox.fr/api/v8/"+method
	return json.loads(requests.get(url, headers=headers).text)
	
def mksession():
# Attention a ne pas avoir de antislash \ dans le token car pb avec 
# {"success":true,"result":{"app_token":"Rv7udEUk2UDT0wKu+E2PxZcEZQGNE3sJxYfGe2IUO5+vqtV+J6UoQsBgbhD5PR0L","track_id":1}}

# {"success":true,"result":{"status":"granted","challenge":"bWJfGdCTh5+onbz0UIArjxbzWDZcVhQY","password_salt":"hZBntmbZ\/WE7UzwfYeoU7aEgquerOG6W"}}

# Revolution 
# token = b'nK3U2oRc6C7JejudI1DNjC87sIw+mY3mFCVmZO+giZHGOciindwk6TK53P5ZCMvk'
# Delta Pop
	app_token =   b"Rv7udEUk2UDT0wKu+E2PxZcEZQGNE3sJxYfGe2IUO5+vqtV+J6UoQsBgbhD5PR0L"
	
	challenge=connexion_get("login/")["result"]["challenge"]
	print("app_token",app_token)
	print("challenge",challenge)
	print("challenge.encode('utf-8')=",challenge.encode('utf-8'))
	data={
		"app_id": "fr.freebox.python",
		"password": hmac.new(app_token,challenge.encode('utf-8'),hashlib.sha1).hexdigest()
	}
	print(data['app_id'])
	print(data['password'])
	resultat = connexion_post("login/session/",data)

	print('resultat=',resultat)
	return resultat["result"]["session_token"]

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


r = requests.get("http://mafreebox.freebox.fr/api/v8/login/")
#print( r.text)

challenge = json.loads(r.text)['result']['challenge']
password_salt = json.loads(r.text)['result']['password_salt']
#print(challenge)
#print(password_salt)

session_token = mksession() #création session API Freebox

textreturned = recherche_app(session_token)

print(textreturned)

