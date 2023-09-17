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

from gcalendar import init_calendar, get_events, update_event, insert_event, delete_event
from apscheduler.schedulers.background import BackgroundScheduler  #this will let us check the calender on a regular interval


baseurl='http://82.66.27.135/domocan/www/php/'

ressource = init_calendar()

def Event_DailyControlShutter(ressource, forecast_daily):

	# print(pytz.timezone('Europe/Paris'))
	by_day = forecast_daily	

	timesunriseTime=datetime.fromtimestamp(  by_day['sunrise'][1], tz=pytz.timezone('Europe/Paris'))
	timesunriseTimeEnd = timesunriseTime + timedelta(hours=1)
	timesunsetTime=datetime.fromtimestamp(  by_day['sunset'][1], tz=pytz.timezone('Europe/Paris')) + timedelta(min=30)
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

def get_forecast():
	url = 'https://api.open-meteo.com/v1/forecast?latitude=48.1212&longitude=-1.603&hourly=temperature_2m,apparent_temperature,cloudcover,cloudcover_low,cloudcover_mid,cloudcover_high&daily=temperature_2m_max,temperature_2m_min,apparent_temperature_max,apparent_temperature_min,sunrise,sunset,precipitation_sum,precipitation_hours,windspeed_10m_max&timezone=auto&forecast_days=1'

	res = requests.get(url)
	data = res.json()
	return data

from statistics import mean

def Event_DailyControlPAC(ressource, forecast):
	index_start =  9 # only daylight values
	index_end =  18 # only daylight values
	mean_cloud = int(mean(forecast['hourly']['cloudcover'][index_start:index_end]))
	mean_cloud_low = int(mean(forecast['hourly']['cloudcover_low'][index_start:index_end]))
	mean_cloud_mid = int(mean(forecast['hourly']['cloudcover_mid'][index_start:index_end]))
	mean_cloud_high = int(mean(forecast['hourly']['cloudcover_high'][index_start:index_end]))
	mean_temp2m = int(mean(forecast['hourly']['temperature_2m']))
	mean_apparenttemp = int(mean(forecast['hourly']['apparent_temperature']))

	timesunsetTime=datetime.now( tz=pytz.timezone('Europe/Paris'))
	timesunsetTimeEnd = timesunsetTime + timedelta(hours=1)

	eventShutter = {
		'summary': 'WakePAC',
		'location': 'Cesson-Sevigne, France',
		'start': {
			'dateTime': timesunsetTime.strftime('%Y-%m-%dT%H:%M:%S%z'), 
		},
		'end': {
			'dateTime':  timesunsetTimeEnd.strftime('%Y-%m-%dT%H:%M:%S%z'), # +60mn
		},
		'description': \
		'time start: '+str(forecast['hourly']['time'][index_start]) \
		+'\napparent_temperature_max: '+str(forecast['daily']['apparent_temperature_max']) \
		+'\napparent_temperature_min: '+str(forecast['daily']['apparent_temperature_min']) \
		+'\nmean_temp2m: '+str(mean_temp2m)+'\nmean_apparenttemp: '+str(mean_apparenttemp)
		+'\nprecipitation_sum: '+str(forecast['daily']['precipitation_sum']) \
		+'\nprecipitation_hours: '+str(forecast['daily']['precipitation_hours']) \
		+'\nwindspeed_10m_max: '+str(forecast['daily']['windspeed_10m_max']) \
		+'\nmean_cloud: '+str(mean_cloud)+'\nmean_cloud_low under 3km: '+str(mean_cloud_low) \
		+'\nmean_cloud_mid 3 to 8km: '+str(mean_cloud_mid)+'\nmean_cloud_high over 8km: '+str(mean_cloud_high) \
		,
	} 
	created_event = insert_event(ressource, param_body=eventShutter)

	return

def Event_WakePAC(event, ressource):
	print( 'ALLUMAGE CHAUFFAGE DONE')

	if(event.get('description')):
		event['description']=event['description']+' \nPAC Started'
	else:
		event['description']='\nPAC Started'

	updated_event = update_event( ressource, param_eventId=event['id'], param_event=event)
	return

def http_post(url):
	url_response = requests.post(url)
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

		time.sleep( 3 )
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
	return

TZ_OFFSET = pytz.timezone('Europe/Paris').localize(datetime.now()).strftime('%z')

def addTempoEventCalendar(couleur, jours):

	if 'BLAN' in couleur['couleurJourJ1']:
		colorId='8'
	if 'ROUGE' in couleur['couleurJourJ1']:
		colorId='11'
	if 'BLEU' in couleur['couleurJourJ1']:
		colorId='9'
	# sendToWhatApp('Demain jour ' + couleur['couleurJourJ1'])

	startTempo=(datetime.now() + timedelta(days=1)).strftime('%Y-%m-%dT')+'06:00:00'+TZ_OFFSET
	endTempo= (datetime.now() + timedelta(days=1, hours=1)).strftime('%Y-%m-%dT')+'06:00:00'+TZ_OFFSET
	# .replace(tzinfo=pytz.timezone('UTC'))
	print(endTempo)
	eventShutter = {
		'summary': couleur['couleurJourJ1'],
		'location': 'Cesson-Sevigne, France',
		'start': {
			'dateTime': startTempo,
	#                         'timeZone': 'Europe/Paris(
		},
		'end': {
			'dateTime':  endTempo, # startOpenShutter+1H
	#                        'timeZone': 'Europe/Paris'
		},
		'description': 'ROUGE:'+str(jours['PARAM_NB_J_ROUGE'])+'\nBLANC:'+str(jours['PARAM_NB_J_BLANC'])+'\nBLEU:'+str(jours['PARAM_NB_J_BLEU']),
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
# From web browser : https://192.168.0.14/domocan/www/php/CmdPAC.php?state=0
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
# From web browser : https://192.168.0.14/domocan/www/php/CmdPAC.php?state=0
	url='https://particulier.edf.fr/services/rest/referentiel/searchTempoStore?dateRelevant=' + date
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
		# print( result['couleurJourJ'] )

# {'couleurJourJ': 'TEMPO_BLANC', 'couleurJourJ1': 'TEMPO_BLANC'}
		return result
	
def Event_TempoControl():
	JourTempo = GetJourTempo()
	CouleurTempo = GetCouleurTempo( datetime.now().strftime('%Y-%m-%d') )
	
	addTempoEventCalendar( CouleurTempo , JourTempo)

def Event_Calendar(ressource):
#We add TZ_OFFSET in timeMin/timeMax
	wake_interval=8
	global event_tempo
	event_tempo=''

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
	for event in events_found:
		print( "!!!MATCH!!!   Title query=%s"%event['summary'])
		print( "Debut : ",event['start'])
		print( "Fin : ",event['end'])

		if( 'TEMPO_' in event['summary']):
			if( 'ROUGE' in event['summary']):
				HP_Rouge_Chauffage_Interdit = True
			else:
				HP_Rouge_Chauffage_Interdit = False
			event_tempo=event['id']
			
		if( event['summary']=='DailyTempo'):
			Event_TempoControl()
			delete_event(ressource, param_eventId=event['id'])

		if( event['summary']=='DailyControl'):
			# What the weather like ?
			forecast = get_forecast()
			Event_DailyControlShutter(ressource, forecast['daily'] )
			Event_DailyControlPAC(ressource, forecast['daily'])
			delete_event(ressource, param_eventId=event['id'])
			if event_tempo:
				delete_event(ressource, param_eventId=event_tempo) # On peut supprimer l'event TEMPO du jour
			#update_profil_CamRue()

# Accessing the response like a dict object with an 'items' key
# returns a list of item objects (events).
		if( event['summary']=='WakePAC'):
			PACStateToSet=1
			Event_WakePAC(event, ressource)

		if( event['summary']=='OpenShutter'):
			Event_OpenShutter()
			delete_event(ressource, param_eventId=event['id'])

		if( event['summary']=='CloseShutter'):
			Event_CloseShutter()
			delete_event(ressource, param_eventId=event['id'])

	SetPACState(PACStateToSet)

def callable_func():
	#os.system("clear") #this is more for my benefit and is in no way necesarry
	if platform.system()=='Linux':
		syslog.syslog('Domocan Calendar started')
	print ("------------start-----------")
	Event_Calendar(ressource)
	print( "-------------end------------")

if __name__ == '__main__':
	# forecast = get_forecast()
	# Event_DailyControlPAC(ressource,forecast)
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


