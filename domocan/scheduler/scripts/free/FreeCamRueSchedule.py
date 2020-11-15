#!/usr/bin/env python
# -*- coding: utf-8 -*-

import requests
import json
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
s = sun(city.observer, date=datetime.date.today(), tzinfo=city.timezone)
print((
    f'Dawn:    {s["dawn"]}\n'
    f'Sunrise: {s["sunrise"]}\n'
    f'Noon:    {s["noon"]}\n'
    f'Sunset:  {s["sunset"]}\n'
    f'Dusk:    {s["dusk"]}\n'
))

sunrise = (s["sunrise"]+datetime.timedelta(minutes=30)).time()
sunset = (s["sunset"]-datetime.timedelta(minutes=30)).time()

# tz = pytz.timezone(city.timezone)
# time_now = datetime.datetime.now(tz)

# if time_now > sunrise and time_now < sunset: # am I in between sunset and dusk?
# 	print("Daylight")
# else:
# 	print("Switch on light")

def connexion_post(method,data=None,headers={}):
	url = "http://mafreebox.freebox.fr/api/v8/"+method
	if data: data = json.dumps(data)
	response = requests.post(url, data=data, headers=headers).text
	resultat = json.loads(response)

	if resultat["success"] != True:
		raise NameError('!!POST call FAILED !!')
		print("Result KO")
	return resultat

def connexion_put(method,data=None,headers={}):
	url = "http://mafreebox.freebox.fr/api/v8/"+method
	if data: data = json.dumps(data) 
	resultat = json.loads(requests.put(url, data=data, headers=headers).text)

	if resultat["success"] != True:
		raise NameError('!!PUT call FAILED !!')
		print("Result KO")
	return resultat

def connexion_get(method, headers={}):
	url = "http://mafreebox.freebox.fr/api/v8/"+method 
	resultat = json.loads(requests.get(url, headers=headers).text)

	if resultat["success"] != True:
		raise NameError('!!GET call FAILED !!')
		print("Result KO")
	return resultat
	
def mksession():
	token = b'nK3U2oRc6C7JejudI1DNjC87sIw+mY3mFCVmZO+giZHGOciindwk6TK53P5ZCMvk'
	challenge=connexion_get("login/")["result"]["challenge"]
	data={
		"app_id": "fr.freebox.testapp",
		"password": hmac.new(token,challenge.encode('utf-8'),hashlib.sha1).hexdigest()
	}

	return connexion_post("login/session/",data)["result"]["session_token"]

def SearchNetworkProfile(name, session_token):
	# method_get = "network_control"
	method_get = "profile"
	resultat =  connexion_get(method_get, headers={"X-Fbx-App-Auth": session_token})
	data = resultat["result"]

	for val in resultat["result"]:
		if "Foscam" in val["name"]:
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
	data = resultat["result"][0]

	method_put = "network_control/"+str(profile_id)+"/rules/"+str(data["id"])
	data["enabled"]= True
	# Round to 30mn
	sunrise_in_s = datetime.timedelta(hours=sunrise.hour, minutes=(sunrise.minute//30)*30, seconds=0).total_seconds()
	sunset_in_s = datetime.timedelta(hours=sunset.hour, minutes=(sunset.minute//30)*30, seconds=0).total_seconds()

	print(datetime.timedelta(seconds=sunrise_in_s))
	print(datetime.timedelta(seconds=sunset_in_s))
	data["start_time"]= int(sunset_in_s)
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

	# NetworkProfile(profile_id, session_token)

	RuleControlCamera(profile_id,session_token)

	Logout(session_token)

if __name__ == '__main__':
	update_profil_CamRue()


