WinterTime = False
'''
Go to https://code.google.com/apis/console/ and:
- create a new project,
- enable access to the calendar api,
- create a "service account" - that will give you an:
	* "email address" which is the service_account_name and
	* a private key that you need to save on you server
- go to the calendar and share it with the "email address" from above

add calendar sharing preference : 644548480350-gpp37sr6u2o4fupattsos6vdia5k2cqj@developer.gserviceaccount.com
'=+ Permission Settings = make changes AND manage sharing


Make sure your time is synchronized.

requirements are: google-api-python-client and pyOpenSSL
		pip install google-api-python-client pyOpenSSL

	sudo pip install httplib2
	sudo pip install oauth2client
	sudo apt-get install python-dev
	sudo apt-get install libffi-dev
	wget ...
	unzip 
	sudo mv My\ Project-ccd7c47ffc0f.p12 privatekey.p12
	sudo chmod 0777 privatekey.p12
	sudo pip install google-api-python-client pyOpenSSL

'''#These are the imports google said to include
import platform
import sys
if platform.system()=='Linux':
	import syslog
#sys.path.append( "/home/pi/.local/lib/python3.7/site-packages" )

# import datetime
#import atom
import getopt
import string
import time

import ssl
import urllib.request
import urllib

import pytz
###LBRimport xe #for the time comparator
# from feed.date.rfc3339 import tf_from_timestamp #also for the comparator
# from feed.date.rfc3339 import timestamp_from_tf #also for the comparator
#from datetime import datetime #for the time on the rpi end

import os, random
import json

import requests
from datetime import datetime, timedelta, timezone
if platform.system()=='Linux':
	sys.path.append('/var/www/domocan/scheduler/scripts/calendar/')
else:
	sys.path.append(os.path.dirname(os.path.abspath(__file__))+'/../calendar/')

from gcalendar import init_calendar, get_events, update_event, insert_event, delete_event, search_delete
if platform.system()=='Linux':
	sys.path.append('/var/www/domocan/scheduler/scripts/tuya/')
else:
	sys.path.append(os.path.dirname(os.path.abspath(__file__))+'/../tuya/')
from tuya import TuyaRelay
if platform.system()=='Linux':
	sys.path.append('/var/www/domocan/scheduler/scripts/free/')
else:
	sys.path.append(os.path.dirname(os.path.abspath(__file__))+'/../free/')
from FreeCamRueSchedule import update_profil_CamRue
from apscheduler.schedulers.background import BackgroundScheduler  #this will let us check the calender on a regular interval


baseurl='http://127.0.0.1/domocan/www/php/'

ressource = init_calendar()

def Event_DailyControlShutter(ressource, forecast_daily):

	# print(pytz.timezone('Europe/Paris'))
	by_day = forecast_daily	

	start_time= datetime.now().strftime('%Y-%m-%dT')+'07:00:00'+pytz.timezone('Europe/Paris').localize(datetime.now()).strftime('%z')
	start_time_timestamp =  time.mktime(datetime.strptime(start_time, "%Y-%m-%dT%H:%M:%S%z").timetuple())
	timesunriseTime = datetime.fromtimestamp(  start_time_timestamp , tz=pytz.timezone('Europe/Paris'))
	timesunriseTime += timedelta(days=1)

	# timesunriseTime=datetime.fromtimestamp(  by_day['sunrise'][1], tz=pytz.timezone('Europe/Paris'))
	timesunriseTimeEnd = timesunriseTime + timedelta(hours=1)
	timesunsetTime=datetime.fromtimestamp(  by_day['sunset'][1], tz=pytz.timezone('Europe/Paris')) + timedelta(minutes=30)
	timesunsetTimeEnd = timesunsetTime + timedelta(hours=1)

# !!!Pour ouverture volet roulant!!!
	eventShutter = {
		'summary': 'OpenShutter',
		'location': 'Cesson-Sevigne, France',
		'start': {
			'dateTime': timesunriseTime.strftime('%Y-%m-%dT%H:%M:%S%z'),
#                         'timeZone': 'Europe/Paris'
		},
		'end': {
			'dateTime':  timesunriseTimeEnd.strftime('%Y-%m-%dT%H:%M:%S%z'), # startOpenShutter+1H
#                        'timeZone': 'Europe/Paris'
		},

	}
	created_event = insert_event(ressource, param_body=eventShutter)

# !!!Pour fermeture volet roulant!!!
	eventShutter = {
		'summary': 'CloseShutter',
		'location': 'Cesson-Sevigne, France',
		'start': {
			'dateTime': timesunsetTime.strftime('%Y-%m-%dT%H:%M:%S%z'), 
		},
		'end': {
			'dateTime':  timesunsetTimeEnd.strftime('%Y-%m-%dT%H:%M:%S%z'), # +60mn
		},
	}
	created_event = insert_event(ressource, param_body=eventShutter)

def get_forecast_soldcast():
	# url = "https://api.solcast.com.au/rooftop_sites/09ea-e387-a5a0-4327/forecasts?format=json&api_key=JVw9GH-MxcOHto74WOfmGcKhRTtTSuPl"
	# res = requests.get(url, headers={"accept": "application/json"})
	# data_soldcast = res.json()
	# for forecas in data_soldcast['forecasts']:
		# print("soldcast:",data_soldcast['forecasts'][forecas])

	return data_soldcast
	
def get_forecast_solar():
	# url = "https://api.forecast.solar/estimate/watthours/day/48.0985285/-1.6038443/30/180/4?time=utc"
	# res = requests.get(url)
	# data_forecast_solar = res.json()
	# count=1
	# if( 'Rate ' not in data_forecast_solar['result'] ):
		# for date in data_forecast_solar['result']:
			# if count > 1:
				# break
			# print("cumul by day :",date,": ",data_forecast_solar['result'][date], "Wh")
			# date_api_forecast_solar = date
			# cumul_Wh_day_api_forecast_solar = data_forecast_solar['result'][date]
			# print('Solar Power for 4Kw panel ',date_api_forecast_solar,': ', cumul_Wh_day_api_forecast_solar)
			# count += 1
	# date_api_forecast_solar = "17/04"
	# cumul_Wh_day_api_forecast_solar = "1000"
	# info_cumul_Wh_day = date_api_forecast_solar+" forecast solar cumul day: "+cumul_Wh_day_api_forecast_solar+" Wh"

	# url = "https://api.forecast.solar/estimate/watthours/48.098529/-1.603844/30/180/4?time=utc"
	# res = requests.get(url, headers={"accept": "application/json"})
	# data_forecast_solar = res.json()
	# if( 'Rate ' not in data_forecast_solar['result'] ):
		# print("forecast.solar:",data_forecast_solar['result'])	
		# for date in data_forecast_solar['result']:
			# print("cumul by hour:",date, data_forecast_solar['result'][date], "Wh")

	# url = "https://api.forecast.solar/estimate/watts/48.0985285/-1.6038443/30/180/4?time=utc"
	# res = requests.get(url)
	# data_forecast_solar = res.json()
	# print(data_forecast_solar['result'])
	# if( 'Rate ' not in data_forecast_solar['result'] ):
		# for date in data_forecast_solar['result']:
			# print("watt_hours_period :",date,": ",data_forecast_solar['result'][date], "Watt")

	url = "https://api.forecast.solar/estimate/48.0985285/-1.6038443/30/180/4"
	res = requests.get(url)
	data_forecast_solar = res.json()
	# print("forecast.solar:",data_forecast_solar)
	# for info in data_forecast_solar['result']:
		# for date in info:
			# print(date,": ",data_forecast_solar['result'][info][date])
	
	return data_forecast_solar
	
def get_forecast():
	url = 'https://api.open-meteo.com/v1/forecast?latitude=48.1212&longitude=-1.603&hourly=shortwave_radiation,direct_radiation,direct_normal_irradiance,diffuse_radiation,global_tilted_irradiance,temperature_2m,apparent_temperature,cloudcover,cloudcover_low,cloudcover_mid,cloudcover_high&daily=sunshine_duration,temperature_2m_max,temperature_2m_min,apparent_temperature_max,apparent_temperature_min,sunrise,sunset,precipitation_sum,precipitation_hours,windspeed_10m_max&timezone=auto&timeformat=unixtime&forecast_days=2&tilt=30'

	res = requests.get(url)
	data_open_meteo = res.json()
	
	return data_open_meteo

from statistics import mean

def Event_DailyControlSolar(ressource, forecast):
	sunrise_timestamp = forecast['daily']['sunrise'][1]
	# timesunriseTime =datetime.fromtimestamp( forecast['daily']['sunrise'][1], tz=pytz.timezone('Europe/Paris'))
	# timesunsetTime =datetime.fromtimestamp( forecast['daily']['sunset'][1], tz=pytz.timezone('Europe/Paris'))
	# print("timesunriseTime",timesunriseTime,"timesunsetTime",timesunsetTime)
	for index_sunrise in range(0,47):
		if(sunrise_timestamp < forecast['hourly']['time'][index_sunrise]):
			index_sunrise+=1
			break
	sunset_timestamp = forecast['daily']['sunset'][1]
	for index_sunset in range(0,47):
		if(sunset_timestamp < forecast['hourly']['time'][index_sunset]):
			break
	# print("index_sunrise:", index_sunrise, "index_sunset:",index_sunset )
	# print("shortwave_radiation:", forecast['hourly']['shortwave_radiation'][index_sunrise:index_sunset] )
	# print("direct_radiation:", forecast['hourly']['direct_radiation'][index_sunrise:index_sunset] )
	# print("direct_normal_irradiance:", forecast['hourly']['direct_normal_irradiance'][index_sunrise:index_sunset] )
	# print("diffuse_radiation:", forecast['hourly']['diffuse_radiation'][index_sunrise:index_sunset] )
	# print("global_tilted_irradiance:", forecast['hourly']['global_tilted_irradiance'][index_sunrise:index_sunset] )

	sunshine_duration = int(forecast['daily']['sunshine_duration'][1])
	shortwave_radiation_mean = int(mean(forecast['hourly']['shortwave_radiation'][index_sunrise:index_sunset]))
	shortwave_radiation_sum = int(sum(forecast['hourly']['shortwave_radiation'][index_sunrise:index_sunset]))
	direct_radiation_mean = int(mean(forecast['hourly']['direct_radiation'][index_sunrise:index_sunset]))
	direct_radiation_sum = int(sum(forecast['hourly']['direct_radiation'][index_sunrise:index_sunset]))
	direct_normal_irradiance_mean = int(mean(forecast['hourly']['direct_normal_irradiance'][index_sunrise:index_sunset]))
	direct_normal_irradiance_sum = int(sum(forecast['hourly']['direct_normal_irradiance'][index_sunrise:index_sunset]))
	diffuse_radiation_mean = int(mean(forecast['hourly']['diffuse_radiation'][index_sunrise:index_sunset]))
	diffuse_radiation_sum = int(sum(forecast['hourly']['diffuse_radiation'][index_sunrise:index_sunset]))
	global_tilted_irradiance_mean = int(mean(forecast['hourly']['global_tilted_irradiance'][index_sunrise:index_sunset]))
	global_tilted_irradiance_sum = int(sum(forecast['hourly']['global_tilted_irradiance'][index_sunrise:index_sunset]))

	timesunriseTime =datetime.fromtimestamp(  forecast['hourly']['time'][index_sunrise], tz=pytz.timezone('Europe/Paris'))
	timesunsetTimeEnd = timesunriseTime + timedelta(hours=1)
	timesunsetTime =datetime.fromtimestamp(  forecast['hourly']['time'][index_sunset], tz=pytz.timezone('Europe/Paris'))

	data_estimates = get_forecast_solar()
	# print(data_estimates)
	dict_data_estimates = data_estimates['result']['watt_hours_day']
	date_watt_hours_day = list(dict_data_estimates)[1]
	# print(date_watt_hours_day)
	watt_hours_day = dict_data_estimates[date_watt_hours_day]
	# print(watt_hours_day)

	startEvent=(datetime.now() + timedelta(days=1)).strftime('%Y-%m-%dT')+'09:00:00'+TZ_OFFSET
	endEvent= (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%dT')+'10:00:00'+TZ_OFFSET

	eventSolar = {
		'summary': str(direct_normal_irradiance_sum)+' W/m2',
		'location': 'Cesson-Sevigne, France',
		'start': {
			'dateTime': startEvent, 
		},
		'end': {
			'dateTime':  endEvent
		},
		'description': \
		'\ndirect_radiation mean W/m²: ' + str(direct_radiation_mean) \
		# +'\nPuiss: ' + str(watt_hours_day) + 'W for day: ' + str(date_watt_hours_day) \
		+'\ndirect_normal_irradiance mean W/m²: ' + str(direct_normal_irradiance_mean) \
		+'\ndiffuse_radiation mean W/m²: ' + str(diffuse_radiation_mean) \
		+'\nglobal_tilted_irradiance mean W/m²: ' + str(global_tilted_irradiance_mean) \
		+'\ndirect_radiation sum W/m²: ' + str(direct_radiation_sum) \
		+'\ndirect_normal_irradiance sum W/m²: ' + str(direct_normal_irradiance_sum) \
		+'\ndiffuse_radiation sum W/m²: ' + str(diffuse_radiation_sum) \
		+'\nglobal_tilted_irradiance sum W/m²: ' + str(global_tilted_irradiance_sum) \
		+'\nsunshine_duration s: ' + str(sunshine_duration) \
		,
	} 
    # "Lavender": 	1,
    # "Sage": 		2,
    # "Grape": 	    3,
    # "Flamingo": 	4,
    # "Banana": 		5,
    # "Tangerine": 	6,
    # "Peacock": 		7,
    # "Graphite": 	8,
    # "Blueberry": 	9,
    # "Basil":        10,
    # "Tomato": 		11
	if ( direct_normal_irradiance_sum <1000 ):
		eventSolar['colorId']=  '5' # Banana
	elif ( direct_normal_irradiance_sum <2000 ):
		eventSolar['colorId']= '6' # Tangerine
	elif ( direct_normal_irradiance_sum <3000 ):
		eventSolar['colorId']= '4' # Flamingo
	elif ( direct_normal_irradiance_sum <4000 ):
		eventSolar['colorId']= '1' # Lavender
	else:
		eventSolar['colorId']= '11' # Tomato

	print(eventSolar)

	created_event = insert_event(ressource, param_body=eventSolar)

	hour_end_2k = int(max(2,(2000-(direct_normal_irradiance_sum))/10))
	hour_end_1k = int(max(2,(4000-(direct_normal_irradiance_sum))/10))
	SetSolarRouter(hour_end_2k, hour_end_1k)
	
	return

def Event_DailyControlPAC(ressource, forecast):
	sunrise_timestamp = forecast['daily']['sunrise'][1]
	# timesunriseTime =datetime.fromtimestamp( forecast['daily']['sunrise'][1], tz=pytz.timezone('Europe/Paris'))
	# timesunsetTime =datetime.fromtimestamp( forecast['daily']['sunset'][1], tz=pytz.timezone('Europe/Paris'))
	# print("timesunriseTime",timesunriseTime,"timesunsetTime",timesunsetTime)
	for index_sunrise in range(0,47):
		if(sunrise_timestamp < forecast['hourly']['time'][index_sunrise]):
			index_sunrise+=1
			break
	sunset_timestamp = forecast['daily']['sunset'][1]
	for index_sunset in range(0,47):
		if(sunset_timestamp < forecast['hourly']['time'][index_sunset]):
			break

	mean_cloud = int(mean(forecast['hourly']['cloudcover'][index_sunrise:index_sunset]))
	mean_cloud_low = int(mean(forecast['hourly']['cloudcover_low'][index_sunrise:index_sunset]))
	mean_cloud_mid = int(mean(forecast['hourly']['cloudcover_mid'][index_sunrise:index_sunset]))
	mean_cloud_high = int(mean(forecast['hourly']['cloudcover_high'][index_sunrise:index_sunset]))
	mean_temp2m = int(mean(forecast['hourly']['temperature_2m'][24:47])) # mean J+1
	mean_apparenttemp = int(mean(forecast['hourly']['apparent_temperature'][24:47]))  # mean J+1

	timesunriseTime =datetime.fromtimestamp(  forecast['hourly']['time'][index_sunrise], tz=pytz.timezone('Europe/Paris'))
	timesunsetTimeEnd = timesunriseTime + timedelta(hours=1)
	timesunsetTime =datetime.fromtimestamp(  forecast['hourly']['time'][index_sunset], tz=pytz.timezone('Europe/Paris'))

	precipitation_sum = forecast['daily']['precipitation_sum'][1]
	# Compute time to heat
	heatTimeMinutes = (60) # min 1H for calendar
	if mean_apparenttemp < 18:
		heatTimeMinutes = (18 - mean_apparenttemp)*20 # 20mn par degre
		heatTimeMinutes += (precipitation_sum*20) # 20mn par mm
		heatTimeMinutes += (mean_cloud)
		if heatTimeMinutes < (120):
			heatTimeMinutes = (120) # min 2H to heat
	print("heatTimeMinutes=",heatTimeMinutes)
	# Limit to not overpass low rate hour
	if heatTimeMinutes> (465): # 8h*60mn-15min
		heatTimeMinutes = 465

	today_start_on_night= datetime.now().strftime('%Y-%m-%dT')+'22:00:00'+pytz.timezone('Europe/Paris').localize(datetime.now()).strftime('%z')
	start_time_timestamp =  time.mktime(datetime.strptime(today_start_on_night, "%Y-%m-%dT%H:%M:%S%z").timetuple())
	today_end_on_night = datetime.fromtimestamp(  start_time_timestamp , tz=pytz.timezone('Europe/Paris'))
	today_end_on_night += timedelta(minutes=heatTimeMinutes)


	eventPAC = {
		'location': 'Cesson-Sevigne, France',
		'start': {
			'dateTime': today_start_on_night, 
		},
		'end': {
			'dateTime':  today_end_on_night.strftime('%Y-%m-%dT%H:%M:%S%z'), # +60mn
		},
		'description': \
		'time start: '+timesunriseTime.strftime('%Y-%m-%dT%H:%M:%S%z') \
		+'\nmean_cloud: '+str(mean_cloud)+'\nmean_cloud_low under 3km: '+str(mean_cloud_low) \
		+'\nmean_cloud_mid 3 to 8km: '+str(mean_cloud_mid)+'\nmean_cloud_high over 8km: '+str(mean_cloud_high) \
		+'\ntime end: '+timesunsetTime.strftime('%Y-%m-%dT%H:%M:%S%z') \
		+'\nheatTimeMinutes: '+str(heatTimeMinutes) \
		+'\n\ntemperature_min: '+str(forecast['daily']['temperature_2m_min'][1]) \
		+'\ntemperature_max: '+str(forecast['daily']['temperature_2m_max'][1]) \
		+'\napparent_temperature_min: '+str(forecast['daily']['apparent_temperature_min'][1]) \
		+'\napparent_temperature_max: '+str(forecast['daily']['apparent_temperature_max'][1]) \
		+'\nmean_temp2m: '+str(mean_temp2m)+'\nmean_apparenttemp: '+str(mean_apparenttemp)
		+'\nprecipitation_sum: '+str(forecast['daily']['precipitation_sum'][1]) \
		+'\nprecipitation_hours: '+str(forecast['daily']['precipitation_hours'][1]) \
		+'\nwindspeed_10m_max: '+str(forecast['daily']['windspeed_10m_max'][1]) \
		,
	} 

	if (mean_apparenttemp<18) and (mean_cloud>50):
		eventPAC['summary']='EndWakePAC'
		eventPAC['colorId']='11' # Rouge
	else:
		eventPAC['summary']='NoWakePAC'
		eventPAC['colorId']='2' # Vert

	created_event = insert_event(ressource, param_body=eventPAC)

	return

def Event_WakePAC(event, ressource):
	# print( 'ALLUMAGE CHAUFFAGE DONE')

	if(event.get('description')):
		event['description']=event['description']+' \nPAC Started'
	else:
		event['description']='\nPAC Started'

	updated_event = update_event( ressource, param_eventId=event['id'], param_event=event)
	return

def http_post(url):
	url_response = requests.post(url, timeout=30)
	#    print url_response.info()
	# html = url_response.ok
	# do something
	#               print html
	url_response.close()  # best practice to close the file

def Event_OpenShutter():
	print( 'Ouverture')

#	if(datetime.isoweekday(datetime.now())<=5): # Only Monday to Friday
	# if(datetime.isoweekday(datetime.now())>10): # Never
	if(datetime.isoweekday(datetime.now())>0): # Always
		url=baseurl+'CmdVR.php?carte=0x06&entree=0x0E&data0=0x26'  # Volet Salon
		http_post(url)

		time.sleep( 8 )
#       if(datetime.isoweekday(datetime.now())<=5): # Only Monday to Friday
#        if(datetime.isoweekday(datetime.now())>10): # Never
	if(datetime.isoweekday(datetime.now())>0): # Always
		url=baseurl+'CmdVR.php?carte=0x06&entree=0x09&data0=0x52' # VR haut cuisine
		http_post(url)

#	if(datetime.isoweekday(datetime.now())<=5): # Only Monday to Friday
	if(datetime.isoweekday(datetime.now())>10): # Never
	# if(datetime.isoweekday(datetime.now())>0): # Always

		time.sleep( 10 )
		url=baseurl+'CmdVR.php?carte=0x0A&entree=0x0B&data0=0x26' # VR Sdb 1
		http_post(url)

		time.sleep( 0.5 )
		url=baseurl+'CmdVR.php?carte=0x02&entree=0x02&data0=0x26' # Chambre 3
		http_post(url)

		time.sleep( 0.5 )
		url=baseurl+'CmdVR.php?carte=0x02&entree=0x07&data0=0x26' # Chambre 4
		http_post(url)

		time.sleep( 0.5 )
		url=baseurl+'CmdVR.php?carte=0x04&entree=0x06&data0=0x26' # VR Fixe Chambre Nord
		http_post(url)

		time.sleep( 0.5 )
		url=baseurl+'CmdVR.php?carte=0x04&entree=0x01&data0=0x26' # VR FChambre Nord
		http_post(url)

		time.sleep( 0.5 )
		url=baseurl+'CmdVR.php?carte=0x0A&entree=0x04&data0=0x26' # VR Fixe Chambre Terrasse
		http_post(url)

		time.sleep( 0.5 )
		url=baseurl+'CmdVR.php?carte=0x0A&entree=0x06&data0=0x26' # VR Chambre Terrasse
		http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x02&entree=0x09&data0=0x26' # VR Sdb 2 + Garage
	http_post(url)
	return

def Event_CloseShutter():
	print('Fermeture Volet')
	url=baseurl+'CmdVR.php?carte=0x06&entree=0x0F&data0=0x26'  # Volet Salon
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x02&entree=0x03&data0=0x26' # Chambre 3
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x02&entree=0x06&data0=0x26' # Chambre 4
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x02&entree=0x09&data0=0x52' # VR Sdb 2 + Garage
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x0A&entree=0x00&data0=0x26' # VR Sdb 1
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x04&entree=0x07&data0=0x26' # VR Fixe Chambre Nord
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x04&entree=0x00&data0=0x26' # VR Chambre Nord
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x0A&entree=0x03&data0=0x26' # VR Fixe Chambre Terrasse
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x0A&entree=0x05&data0=0x26' # VR Chambre Terrasse
	http_post(url)

	time.sleep( 0.5 )
	url=baseurl+'CmdVR.php?carte=0x06&entree=0x08&data0=0x52' # VR haut cuisine
	http_post(url)
	return

def SetPACState(PACStateToSet):
	if PACStateToSet:
		url=baseurl+'CmdPAC.php?state=1'  # PAC ON
	else:
		url=baseurl+'CmdPAC.php?state=0'  # PAC OFF

	http_post(url)

	return

def SetSmartEVSE(state):
	url="http://192.168.1.102/settings?mode="+str(state) # VR haut cuisine
	http_post(url)
	return

def SetSolarRouter(endECS_On_2K, endECS_On_1K):
	url="http://192.168.1.125/ActionsUpdateAction?NumAction=1&periode=1&value="+str(endECS_On_2K).zfill(4) # heure fin chauffe heure nuit
	http_post(url)
	print(url)
	
	time.sleep( 3 )

	url="http://192.168.1.125/ActionsUpdateAction?NumAction=2&periode=1&value="+str(endECS_On_1K).zfill(4) # heure fin chauffe heure nuit
	http_post(url)
	print(url)
	return


TZ_OFFSET = pytz.timezone('Europe/Paris').localize(datetime.now()).strftime('%z')

def addTempoEventCalendar(couleur, jours):

	print(couleur)
	# if 'BLAN' in couleur['couleurJourJ1']:
	if 'WHITE' in couleur:
		colorId='8'
		couleur = 'TEMPO_BLANC'
	# if 'ROUGE' in couleur['couleurJourJ1']:
	if 'RED' in couleur:
		colorId='11'
		couleur = 'TEMPO_ROUGE'
	# if 'BLEU' in couleur['couleurJourJ1']:
	if 'BLUE' in couleur:
		colorId='9'
		couleur = 'TEMPO_BLEU'
	# sendToWhatApp('Demain jour ' + couleur['couleurJourJ1'])

	startTempo=(datetime.now() + timedelta(days=1)).strftime('%Y-%m-%dT')+'06:00:00'+TZ_OFFSET
	endTempo= (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%dT')+'22:00:00'+TZ_OFFSET
	# .replace(tzinfo=pytz.timezone('UTC'))
	print(endTempo)
	eventShutter = {
		# 'summary': couleur['couleurJourJ1'],
		'summary': str(couleur),
		'location': 'Cesson-Sevigne, France',
		'start': {
			'dateTime': startTempo,
	#                         'timeZone': 'Europe/Paris(
		},
		'end': {
			'dateTime':  endTempo, # startOpenShutter+1H
	#                        'timeZone': 'Europe/Paris'
		},
		# 'description': 'ROUGE:'+str(jours['PARAM_NB_J_ROUGE'])+'\nBLANC:'+str(jours['PARAM_NB_J_BLANC'])+'\nBLEU:'+str(jours['PARAM_NB_J_BLEU']),
		'description': 'TEMPO:'+str(couleur),
 # Red background. Use Calendar.Colors.get() for the full list.
		'colorId': colorId,
	'reminders': {
		'useDefault': False,
		'overrides': [
		  # {'method': 'email', 'minutes': 24 * 60},
		  {'method': 'popup', 'minutes': 24 * 60},
		],
	},
	}

	created_event = insert_event(ressource, param_body=eventShutter)
	print(  created_event   )

def GetJourTempo():
	url='https://particulier.edf.fr/services/rest/referentiel/getNbTempoDays?TypeAlerte=TEMPO'
	try:
		# print( url)
		url_response = urllib.request.urlopen(url, timeout=5)
	except urllib.error.URLError as e:
		print(e.reason)
	else:
		# print( url_response.info())
		html = url_response.read()
# do something
		url_response.close()  # best practice to close the file
		strtempo=html.decode('ascii')

		result = json.loads(strtempo)
		# print( result['PARAM_NB_J_BLANC'])

# {'PARAM_NB_J_BLANC': 24, 'PARAM_NB_J_ROUGE': 7, 'PARAM_NB_J_BLEU': 177}
		return result

def GetCouleurTempo(date):
	# url='https://particulier.edf.fr/services/rest/referentiel/searchTempoStore?dateRelevant=' + date
	url = 'https://www.services-rte.com/cms/open_data/v1/tempo?season=2024-2025'
	try:
		# print( url)
		url_response = urllib.request.urlopen(url, timeout=5)
	except urllib.error.URLError as e:
		print(e.reason)
	else:
		html = url_response.read()
# do something
		url_response.close()  # best practice to close the file
		strtempo=html.decode('ascii')

		result = json.loads(strtempo)
		# print( date )
		# print( result['values'][date] )

# {'couleurJourJ': 'TEMPO_BLANC', 'couleurJourJ1': 'TEMPO_BLANC'}
		return result['values'][date], date
	
def Event_TempoControl():
	# obsolete JourTempo = GetJourTempo()
	CouleurTempo, JourTempo = GetCouleurTempo( datetime.now().strftime('%Y-%m-%d') )
	
	addTempoEventCalendar( CouleurTempo , JourTempo)

def Event_Calendar(ressource):
#We add TZ_OFFSET in timeMin/timeMax
	wake_interval=8
	HP_Rouge_Chauffage_Interdit = False

	timeMin=(datetime.now()+ timedelta(minutes=-(wake_interval/2))).isoformat()+TZ_OFFSET
	timeMax=(datetime.now()+ timedelta(minutes=+(wake_interval/2)+1)).isoformat()+TZ_OFFSET

# add timezone
	# print('timeMin=%s'%timeMin,'ajouter offset\n')
	# print('timeMax=%s'%timeMax,'ajouter offset\n')
	# print(' Getting all events from this calendar ID')
#    pprint.pprint(events)

# Accessing the response like a dict object with an 'items' key
# returns a list of item objects (events).
	events_found = get_events(ressource, timeMin, timeMax)
	PACStateToSet=0
	ChargeEV=0
	HP_Rouge_Chauffage_Interdit = False
	# forecast = get_forecast()
	# Event_DailyControlSolar(ressource, forecast)
	for event in events_found:
		print( "!!!MATCH!!!   Title query=%s"%event['summary'])
		print( "Debut : ",event['start'])
		print( "Fin : ",event['end'])

		if('TEMPO_ROUGE' in event['summary']) or ('TEMPO_BLANC' in event['summary']):
			HP_Rouge_Chauffage_Interdit = True
			
		if( event['summary']=='DailyTempo'):
			update_profil_CamRue()
			search_delete(ressource, "TEMPO_")
			Event_TempoControl()
			delete_event(ressource, param_eventId=event['id'])

		if( event['summary']=='DailyControl'):
			# What the weather like ?
			forecast = get_forecast()
			Event_DailyControlShutter(ressource, forecast['daily'] )
			Event_DailyControlPAC(ressource, forecast)
			Event_DailyControlSolar(ressource, forecast)
			delete_event(ressource, param_eventId=event['id'])
			#update_profil_CamRue()

		if( event['summary']=='SolarForecast'):
			# What the weather like ?
			forecast = get_forecast()
			Event_DailyControlSolar(ressource, forecast)
			delete_event(ressource, param_eventId=event['id'])

# Accessing the response like a dict object with an 'items' key
# returns a list of item objects (events).
		if( event['summary']=='SmartEVSEmodeOff'):
			ChargeEV=0 # Off
		if( event['summary']=='SmartEVSEmodeNormal'):
			ChargeEV=1 # Normal
		if( event['summary']=='SmartEVSEmodeSolar'):
			ChargeEV=2 # Solar
		if( event['summary']=='SmartEVSEmodeSmart'):
			ChargeEV=3 # Smart

		if( event['summary']=='WakePAC'):
			PACStateToSet=1
			# Event_WakePAC(event, ressource)

		if( event['summary']=='OpenShutter'):
			# during holidays Event_OpenShutter()
			delete_event(ressource, param_eventId=event['id'])

		if( event['summary']=='CloseShutter'):
			Event_CloseShutter()
			delete_event(ressource, param_eventId=event['id'])

	session = TuyaRelay()
	session.setRelay(HP_Rouge_Chauffage_Interdit)
	SetPACState(PACStateToSet)
	# LBR SetSmartEVSE(ChargeEV)

def callable_func():
	#os.system("clear") #this is more for my benefit and is in no way necesarry
	if platform.system()=='Linux':
		syslog.syslog('Domocan Calendar started')
	print ("------------start-----------")
	Event_Calendar(ressource)
	print( "-------------end------------")

if __name__ == '__main__':
	# forecast = get_forecast()
	# Event_DailyControlShutter(ressource,forecast['daily'])
	if len(sys.argv)>1:
		scheduler = sys.argv[1]
	else:
		scheduler =  0
	print('Scheduler:',scheduler)

	if(scheduler==1):
		print('Scheduler Activated')
		scheduler = BackgroundScheduler()
		scheduler.add_job(callable_func, 'interval', minutes=2)
		scheduler.start()
#		try:
			# This is here to simulate application activity (which keeps the main thread alive).
#			while True:
#				time.sleep(2)
#		except (KeyboardInterrupt, SystemExit):
			# Not strictly necessary if daemonic mode is enabled but should be done if possible
#			scheduler.shutdown()
	else:
		callable_func() #if we don't use scheduler, direct call


