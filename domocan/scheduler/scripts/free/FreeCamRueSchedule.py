#!/usr/bin/env python
# -*- coding: utf-8 -*-

import requests
import json
import base64
import hmac
import hashlib
import datetime
from astral import LocationInfo
from astral.sun import sun

# t0 = datetime.datetime.combine(datetime.date.today(), datetime.time(0, 0))
# for x in range(0, 48):
# 	print (t0+datetime.timedelta(minutes=(x*30)))

import pytz
utc=pytz.UTC
city = LocationInfo("Paris", "France","Europe/Paris",48.0982983,-1.6051953)
print(city.timezone)
s = sun(city.observer, date=datetime.date.today(), tzinfo=city.timezone)
print((
    f'Dawn:    {s["dawn"]}\n'
    f'Sunrise: {s["sunrise"]}\n'
    f'Noon:    {s["noon"]}\n'
    f'Sunset:  {s["sunset"]}\n'
    f'Dusk:    {s["dusk"]}\n'
))

sunrise = (s["sunrise"]+datetime.timedelta(minutes=30)).time()
dusk = (s["dusk"]-datetime.timedelta(minutes=30)).time()
# print(s["sunrise"]+datetime.timedelta(minutes=30))
# print((s["dusk"]-datetime.timedelta(minutes=30)).time())
# tz = pytz.timezone(city.timezone)
# time_now = datetime.datetime.now(tz)

# if time_now > sunrise and time_now < sunset: # am I in between sunset and dusk?
# 	print("Daylight")
# else:
# 	print("Switch on light")

def connexion_post(method,data=None,headers={}):
	url = "http://mafreebox.freebox.fr/api/v10/"+method
	print("urlPOST=",url)
	if data: data = json.dumps(data)
	response = requests.post(url, data=data, headers=headers).text
	resultat = json.loads(response)

	if resultat["success"] != True:
		raise NameError('!!POST call FAILED !!')
		print("Result KO")
	print("resultatPOST=", resultat)
	return resultat

def connexion_put(method,data=None,headers={}):
	url = "http://mafreebox.freebox.fr/api/v10/"+method
	print("urlPUT=",url)
	if data: data = json.dumps(data) 
	resultat = json.loads(requests.put(url, data=data, headers=headers).text)

	if resultat["success"] != True:
		raise NameError('!!PUT call FAILED !!')
		print("Result KO")
	print("resultatPUT=", resultat)
	return resultat

def connexion_get(method, headers={}):
	url = "http://mafreebox.freebox.fr/api/v10/"+method 
	print("urlGET=",url)
	resultat = json.loads(requests.get(url, headers=headers).text)

	if resultat["success"] != True:
		raise NameError('!!GET call FAILED !!')
		print("Result KO")
	print("resultatGet=", resultat)
	return resultat
	
def mksession():
# Attention a ne pas avoir de antislash \ dans le token car pb avec 
# {"success":true,"result":{"app_token":"Rv7udEUk2UDT0wKu+E2PxZcEZQGNE3sJxYfGe2IUO5+vqtV+J6UoQsBgbhD5PR0L","track_id":1}}

# {"success":true,"result":{"status":"granted","challenge":"bWJfGdCTh5+onbz0UIArjxbzWDZcVhQY","password_salt":"hZBntmbZ\/WE7UzwfYeoU7aEgquerOG6W"}}

# Revolution 
# token = b'nK3U2oRc6C7JejudI1DNjC87sIw+mY3mFCVmZO+giZHGOciindwk6TK53P5ZCMvk'
# Delta Pop
	app_token =   b"Rv7udEUk2UDT0wKu+E2PxZcEZQGNE3sJxYfGe2IUO5+vqtV+J6UoQsBgbhD5PR0L"
	
	resultat = connexion_get("login/")
	# print('resultat=',resultat)
	challenge = resultat["result"]["challenge"]
	print('challenge=',challenge)


	data={
		"app_id": "fr.freebox.python",
		"password": hmac.new(app_token,challenge.encode('utf-8'),hashlib.sha1).hexdigest()
	}
	print('data=',data)

	# resultat = connexion_post("login/session/",data)["result"]["session_token"]
	resultat = connexion_post("login/session/",data)
	# print('resultat=',resultat)
	return resultat["result"]["session_token"]
	
def SearchNetworkProfile(name, session_token):
	# method_get = "network_control"
	method_get = "profile"
	resultat =  connexion_get(method_get, headers={"X-Fbx-App-Auth": session_token})
	data = resultat["result"]
	print("SearchNetworkProfile=",resultat)

	# Pensez a creer un profil Foscam
	for val in resultat["result"]:
		if "Foscam" in val["name"]:
			print("id=", val["id"])
			return val["id"]

def NetworkProfile(profile_id, session_token):
	#method_get = "profile"
	# method_get = "parental/config/"
	# method_get = "parental/filter/1"
	# method_get = "profile/1"
	method_get = "network_control/"+str(profile_id)
	resultat =  connexion_get(method_get, headers={"X-Fbx-App-Auth": session_token})
	data = resultat["result"]

	method_put = "network_control/"+str(profile_id)
	data["override_mode"]= "denied"
	#data["override"]= False
	res2 =  connexion_put(method_put, data, headers={"X-Fbx-App-Auth": session_token})

	method_get = "network_control/"+str(profile_id)
	resultat =  connexion_get(method_get, headers={"X-Fbx-App-Auth": session_token})
	data = resultat["result"]

	return res2

def RuleControlCamera(profile_id,session_token):
	method_get = "network_control/"+str(profile_id)+"/rules"

	resultat =  connexion_get(method_get, headers={"X-Fbx-App-Auth": session_token})
	# Pensez a mettre une pause planifié dans le profil Foscam sinon pas de tableau [0]
	data = resultat["result"][0]
	print("data=",data)

	method_put = "network_control/"+str(profile_id)+"/rules/"+str(data["id"])
	data["enabled"]= True
	# Round to 30mn
	sunrise_in_s = (datetime.timedelta(hours=sunrise.hour, minutes=(sunrise.minute//30)*30, seconds=0)).total_seconds()
	dusk_in_s = (datetime.timedelta(hours=dusk.hour, minutes=(dusk.minute//30)*30, seconds=0)).total_seconds()

	print(datetime.timedelta(seconds=sunrise_in_s))
	print(datetime.timedelta(seconds=dusk_in_s))
	data["start_time"]= int(dusk_in_s)
	data["end_time"]= int(sunrise_in_s)

	res2 =  connexion_put(method_put, data, headers={"X-Fbx-App-Auth": session_token})

	print("Camera Time Configured ", res2['success'])

def Logout( session_token):
	method_get = "login/logout/"
	resultat =  connexion_post(method_get, headers={"X-Fbx-App-Auth": session_token})
	print("LOGOUT Done")

def update_profil_CamRue():
	session_token = mksession() #creation session API Freebox

	profile_id = SearchNetworkProfile("Foscam", session_token)

	NetworkProfile(profile_id, session_token)

	RuleControlCamera(profile_id,session_token)

	Logout(session_token)

if __name__ == '__main__':
   
	update_profil_CamRue()


