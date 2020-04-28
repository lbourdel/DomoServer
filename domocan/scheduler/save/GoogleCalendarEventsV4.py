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
  wget http://domocan35.free.fr/APICalendarKey/CalendarAPIKey.zip
  unzip CalendarAPIKey.zip
  sudo mv My\ Project-ccd7c47ffc0f.p12 privatekey.p12
  sudo chmod 0777 privatekey.p12
  sudo pip install google-api-python-client pyOpenSSL

https://console.developers.google.com/project/apps~valiant-marker-684/apiui/credential
service_account_name = 644548480350-gpp37sr6u2o4fupattsos6vdia5k2cqj@developer.gserviceaccount.com

API KEY = AIzaSyCrNT6qZNeCU930nsGDeQi7YSWxfsIOHDw
'''#These are the imports google said to include
import datetime
import atom
import getopt
import sys
import string
import time

import urllib


###LBRimport xe #for the time comparator
###LBRfrom feed.date.rfc3339 import tf_from_timestamp #also for the comparator
###LBRfrom feed.date.rfc3339 import timestamp_from_tf #also for the comparator
#from datetime import datetime #for the time on the rpi end
###LBRfrom apscheduler.scheduler import Scheduler #this will let us check the calender on a regular interval

#from metar import Metar

import os, random #to play the mp3 later

# For forecastio
import forecastio
import  pickle
#For calendar API V3
import pprint
import pytz
from datetime import datetime, timedelta

#import httplib2
from gcalendar import init_calendar, get_events

from googleapiclient.discovery import build
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request

# If modifying these scopes, delete your previously saved credentials
# at ~/.credentials/calendar-python-quickstart.json
SCOPES = 'https://www.googleapis.com/auth/calendar'
CLIENT_SECRET_FILE = 'client_secret.json'
APPLICATION_NAME = 'Google Calendar API Python Quickstart'

def get_credentials():
    """Gets valid user credentials from storage.

    If nothing has been stored, or if the stored credentials are invalid,
    the OAuth2 flow is completed to obtain the new credentials.

    Returns:
        Credentials, the obtained credential.
    """
    home_dir = os.path.expanduser('~')
    credential_dir = os.path.join(home_dir, '.credentials')
    if not os.path.exists(credential_dir):
        os.makedirs(credential_dir)
    credential_path = os.path.join(credential_dir,
                                   'calendar-python-quickstart.json')

    store = Storage(credential_path)
    credentials = store.get()
    if not credentials or credentials.invalid:
        flow = client.flow_from_clientsecrets(CLIENT_SECRET_FILE, SCOPES)
        flow.user_agent = APPLICATION_NAME
        if flags:
            credentials = tools.run_flow(flow, store, flags)
        else: # Needed only for compatibility with Python 2.6
            credentials = tools.run(flow, store)
        print('Storing credentials to ' + credential_path)
    return credentials
   #from apiclient.discovery import build
   #from oauth2client.client import SignedJwtAssertionCredentials

   #END For calendar API V3
#import urllib2

forecast_api_key = "xxxxxxxxxxx"
# Cesson-Sevigne
lat = 48.0985 
lng = -1.6039
#https://api.forecast.io/forecast/465485026097be2ee6c9bf09ae9e6020/48.0985,-1.6039?lang=fr&units=si

#this is more stuff google told me to do, but essentially it handles the login credentials

wake_interval=8

#OLDdef FullTextQuery(calendar_service):
def FullTextQuery():


    service_account_name = 'xxxxxxxxx'
    calendarId = 'xxx'

# Create an httplib2.Http object to handle our HTTP requests and authorize it
# with our good Credentials.
#OLD    credentials = get_credentials()
#OLD    creds = get_credentials()
#OLD    http = credentials.authorize(httplib2.Http())
# Build a service object for interacting with the API. Visit
# the Google Developers Console

    creds = init_calendar()

#We add TZ_OFFSET in timeMin/timeMax
    TZ_OFFSET = pytz.timezone('Europe/Paris').localize(datetime.now()).strftime('%z')

    timeMin=(datetime.now()+ timedelta(minutes=-(wake_interval/2))).isoformat()+TZ_OFFSET
    timeMax=(datetime.now()+ timedelta(minutes=+(wake_interval/2)+1)).isoformat()+TZ_OFFSET

# add timezone
    print('timeMin=%s'%timeMin,'ajouter offset\n')
    print('timeMax=%s'%timeMax,'ajouter offset\n')

    print(' Getting all events from this calendar ID')
#    pprint.pprint(events)
#OLD    request = events.list(calendarId=calendarId, timeMin=timeMin, timeMax=timeMax, orderBy="startTime",singleEvents=True)

    while (request != None):
# Get the next page
#OLD      response = request.execute(http)
      response = request.execute()
      pprint.pprint( response.get('items'))

# Accessing the response like a dict object with an 'items' key
# returns a list of item objects (events).
      events_found = get_events(creds, timeMin, timeMax)
      for event in events_found:
        print( 'LBR'+event['summary'])

        if( event['summary']=='DailyControl'):
                 print("!!!MATCH!!!   Title query=%s"%event['summary'])
                 print("Debut : ",event['start'])
                 print("Fin : ",event['end'])

# START FROM FORECASTIO
                 forecast = forecastio.load_forecast(forecast_api_key, lat, lng, units= 'si' )

                 WeatherString = ''
                 print("===========Currently Data=========")

                 WeatherString = WeatherString + "\n===========Hourly Data========="
                 by_hour = forecast.hourly()
                 data =forecast.hourly().data
#                 WeatherString = WeatherString + "\nHourly Summary: %s" % (by_hour.summary)

                 by_day = forecast.daily()

#    for hourlyData in by_hour.data:
                 startWeatherString = False # Only to reset when daynumber=0
                 for daynumber in range(0,2):
                     mean_CloudCover=0
                     timesunriseTime = time.strptime('%s'%by_day.data[daynumber].sunriseTime, "%Y-%m-%d %H:%M:%S")
                     timesunsetTime = time.strptime('%s'%by_day.data[daynumber].sunsetTime, "%Y-%m-%d %H:%M:%S")
                     timesunriseTime=time.strftime('%Y-%m-%dT%H:%M:%S', timesunriseTime)
                     timesunsetTime=time.strftime('%Y-%m-%dT%H:%M:%S', timesunsetTime)

                     timesunriseTime=tf_from_timestamp(timesunriseTime)+10800 # +3H apres lever
                     timesunsetTime=tf_from_timestamp(timesunsetTime)-7200 # -2H avant coucher
                     cmpt=0
                     for index in range(0,48):
                        timestart = time.strptime('%s'%data[index].time, "%Y-%m-%d %H:%M:%S")
                        timestart=time.strftime('%Y-%m-%dT%H:%M:%S', timestart)
                        timestart=tf_from_timestamp(timestart)
                        if((timestart>timesunriseTime) and (timestart<timesunsetTime)):
#                            print data[index].time
                            print(data[index].time," : ",data[index].cloudCover)
                            cmpt=cmpt+1
                            mean_CloudCover = mean_CloudCover + data[index].cloudCover
                            # On ouvre que quand cloudCover<0.5
                            if (data[index].cloudCover<0.5):     # or (startWeatherString==True):
                                if(daynumber==0):
                                    WeatherString = WeatherString + '\n%s Temp=%s precipProbability=%s  humidity=%s pressure=%s cloudCover=%s'%(
    data[index].time,data[index].temperature,data[index].precipProbability,data[index].humidity,data[index].pressure,data[index].cloudCover)
                                    if(startWeatherString == False): 
                                        startOpenShutter1=data[index].time
                                        # Not used now !!! startWeatherString = True
                     if cmpt!=0:
                        by_day.data[daynumber].cloudCover=mean_CloudCover/cmpt
                     else:
                        by_day.data[daynumber].cloudCover=1
                     print("mean_CloudCover: ",by_day.data[daynumber].cloudCover)

#    for hourly_data_point in by_hour.data:
#        print hourly_data_point
                 if( startWeatherString == True):
#                     print startOpenShutter1
                     timestart = time.strptime('%s'%startOpenShutter1, "%Y-%m-%d %H:%M:%S")
#                     print timestart

                     startShutter=time.strftime('%Y-%m-%dT%H:%M:%S', timestart)
                     startShutter=startShutter+TZ_OFFSET
#                     datetime.startShutter().strftime('%Y-%m-%dT')+'23:30:00+02:00'

#                     print startShutter
# !!!Pour ouverture volet roulant!!!

                     eventShutter = {
                       'summary': 'OpenShutter',
                       'location': 'Cesson-Sevigne, France',
                       'start': {
                         'dateTime': startShutter,
#                         'timeZone': 'Europe/Paris'
                       },
                       'end': {
                         'dateTime':  timestamp_from_tf( tf_from_timestamp(startShutter)+3600), # startOpenShutter+1H
#                        'timeZone': 'Europe/Paris'
                       },
#                       'attendees': [
#                         {
#                           'email': 'lbourdel@yahoo.fr',
#                           # Other attendee's data...
#                         },
#                         # ...
#                       ],
                     }

                     created_event = service.events().insert(calendarId=calendarId, body=eventShutter).execute()


# cloudCover: A numerical value between 0 and 1 (inclusive) representing the percentage of sky occluded by clouds.
# A value of 0 corresponds to clear sky, 0.4 to scattered clouds,
# 0.75 to broken cloud cover,
# and 1 to completely overcast skies.

                 WeatherString = WeatherString +  "\n===========Daily Summary========="

#                 for daily_data_point in by_day.data:
                 for daynumber in range(0,2):
#        print daily_data_point
                     WeatherString = WeatherString +  '\nSunrise=%s  Sunset=%s'%(
by_day.data[daynumber].sunriseTime, by_day.data[daynumber].sunsetTime)
                     WeatherString = WeatherString +  '\nTempMin=%s Apparent=%s\nTempMax=%s Apparent=%s\nprecipIntensity (mm)=%s humidity=%s\npressure=%s CloudCover= %s\n'%(
#daily_data_point.temperatureMin, daily_data_point.temperatureMax, daily_data_point.precipIntensity, daily_data_point.humidity, daily_data_point.pressure, daily_data_point.cloudCover)
by_day.data[daynumber].temperatureMin, by_day.data[daynumber].apparentTemperatureMin, by_day.data[daynumber].temperatureMax, by_day.data[daynumber].apparentTemperatureMax, by_day.data[daynumber].precipIntensity*25.4, by_day.data[daynumber].humidity, by_day.data[daynumber].pressure, by_day.data[daynumber].cloudCover)
#       print daily_data_point.summary

                 #timestartAtLeast = datetime(year= timestart.tm_year, month= timestart.tm_mon, day= timestart.tm_mday, hour= timestart.tm_hour, minute= timestart.tm_min, tzinfo=None)
                 timestartAtLeast = by_day.data[1].sunriseTime + timedelta(hours=2) #delta +2H en ete +1H en hiver
                 timestart = time.strptime('%s'%timestartAtLeast, "%Y-%m-%d %H:%M:%S")
                 #print('"line:270 timestart OpenShutter:",timestart)

#                 if(((timestart.tm_hour<8) and (timestart.tm_min<30)) or (timestart.tm_hour<7)): # en hiver
					
                 if(((timestart.tm_hour<=7) and (timestart.tm_min<30)) or (timestart.tm_hour<=7)):  # en ete
#                     startShutter= time.strftime('%Y-%m-%dT', timestart)+'08:00:00'
                     startShutter= time.strftime('%Y-%m-%dT', timestart)+'07:40:00' # en ete
                 else:
                     startShutter=time.strftime('%Y-%m-%dT%H:%M:%S', timestart)
                 startShutter=startShutter +TZ_OFFSET 

                 print( "startShutter OpenShutter:",startShutter)

#                 start_timestamp = tf_from_timestamp(startShutter) -2700  #45mn avant lever (45*60=2700)
                 start_timestamp = tf_from_timestamp(startShutter)

#                 print WeatherString
                 eventShutter = {
                   'summary': 'OpenShutter',
                   'location': 'Cesson-Sevigne, France',
                   'start': {
                     'dateTime': timestamp_from_tf( start_timestamp), 
                   },
                   'end': {
                     'dateTime':  timestamp_from_tf( start_timestamp+3600), # startOpenShutter+30mn
                   },
                 }

#Pas pendant vacances                 
                 created_event = service.events().insert(calendarId=calendarId, body=eventShutter).execute()

                 timestartAtLeast = by_day.data[1].sunsetTime + timedelta(hours=2) #delta +1H en hiver +2H en ete
                 timestart = time.strptime('%s'%timestartAtLeast, "%Y-%m-%d %H:%M:%S")

                 startShutter=time.strftime('%Y-%m-%dT%H:%M:%S', timestart)
                 startShutter=startShutter+TZ_OFFSET
                 print ("CloseShutter",by_day.data[1].sunsetTime)
                 print ("timestart:",timestart)
                 print ("startShutter:",startShutter)

                 start_timestamp = tf_from_timestamp(startShutter) + 1200 # 0mn apres coucher (0*60=0s.)

#                 if( (datetime.isoweekday(datetime.now())==1) ): #or (datetime.isoweekday(datetime.now())==3) ): # Only Tuesday
#                     start_timestamp = start_timestamp-5200;

#                 if( (datetime.isoweekday(datetime.now())==3) ): # Only Thursday
#                     start_timestamp = start_timestamp-7200;

                 print( timestamp_from_tf( start_timestamp))
                 eventShutter = {
                   'summary': 'CloseShutter',
                   'location': 'Cesson-Sevigne, France',
                   'start': {
                     'dateTime': timestamp_from_tf( start_timestamp), 
                   },
                   'end': {
                     'dateTime':  timestamp_from_tf( start_timestamp+3600), # +60mn
                   },
                 }

                 created_event = service.events().insert(calendarId=calendarId, body=eventShutter).execute()
# END FROM FORECASTIO

# ajustement fin chauffage
# debut a 23H30 par defaut
# fin = debut + 1/4H en dessous de 15
                 delta=60*60*2 # mini 2H LBR
                 if(by_day.data[1].cloudCover>0.5): # Si beaucoup de soleil le lendemain chauffe que 2H
                    delta=delta+(18-by_day.data[1].apparentTemperatureMin)*20*60 # 20mn par degre
                 print('by_day.data[1].apparentTemperatureMin=%s'%by_day.data[1].apparentTemperatureMin)
                 #print 'Delta=%ss ou %s mn'%(delta,delta/60) #timestamp unit

# If night heat more than 23:30 to 07:15, heat on afternoon
                 if( (delta > (7*60*60+45*60)) and (by_day.data[0].cloudCover>0.7) and (by_day.data[1].cloudCover>0.7) ):  # 7:45
                    delta1=delta-(7*60*60+45*60)
                    delta=(7*60*60+45*60)
                    print('delta1=%ss ou %s mn'%(delta1,delta1/60)) #timestamp unit
                    today_start_off_peak_hour= datetime.now().strftime('%Y-%m-%dT')+'16:00:00'+TZ_OFFSET
                    start_time_timestamp =  tf_from_timestamp(today_start_off_peak_hour)
                    if( delta1 < (3*60*60) ):  # 3:00
                       delta1 = (3*60*60)
                    today_end_off_peak_hour=  timestamp_from_tf(start_time_timestamp + delta1) # +residus
                    eventWakePAC = {
                      'summary': 'WakePAC',
                      'location': 'Cesson-Sevigne, France',
                      'description': 'daylight wake',
                      'start': {
                        'dateTime': today_start_off_peak_hour,
                      },
                      'end': {
                        'dateTime':  today_end_off_peak_hour,
                      },
                    }

                    created_event = service.events().insert(calendarId=calendarId, body=eventWakePAC).execute()

#Arret a la fin de l'heure creuse 07h30
                 today_end_off_peak_hour= datetime.now().strftime('%Y-%m-%dT')+'07:15:00'+TZ_OFFSET
                 end_time_timestamp =  tf_from_timestamp(today_end_off_peak_hour)
                 today_end_off_peak_hour=  timestamp_from_tf(end_time_timestamp + 24*60*60) # +24H
#Calcul duree chauffage
                 today_start_off_peak_hour = timestamp_from_tf(end_time_timestamp + 24*60*60 - delta)
                 print( 'today_start_off_peak_hour=%s'%today_start_off_peak_hour)
                 print( 'today_end_off_peak_hour=%s'%today_end_off_peak_hour)


                 event['start']['dateTime']=today_start_off_peak_hour
#                 event['start']['timeZone']='Europe/Paris'

                 event['end']['dateTime']=today_end_off_peak_hour
#                 event['end']['timeZone']='Europe/Paris'

                 event['description']=WeatherString

                 event['summary']='WakePAC'

                 if( by_day.data[1].temperatureMax < 18 ):
                    updated_event = service.events().update(calendarId=calendarId, eventId=event['id'], body=event).execute()
                 else:
                    event['start']['dateTime']=timestamp_from_tf(end_time_timestamp + 24*60*60 - 60*60)
                    event['summary']='NoWakePAC'
                    updated_event = service.events().update(calendarId=calendarId, eventId=event['id'], body=event).execute()
#                    service.events().delete(calendarId=calendarId, eventId=event['id']).execute()
# Print the updated date.
                 print('------------EVENT UPDATED---------------')

    # the list_next method.
      request = service.events().list_next(request, response)


    print(' Getting all events names WakePAC from this calendar ID')
    PACStateToSet='0'

    request = events.list(calendarId=calendarId, timeMin=timeMin, timeMax=timeMax, orderBy="startTime",singleEvents=True)
    while (request != None):
# Get the next page
#OLD      response = request.execute(http)
      response = request.execute()
# Accessing the response like a dict object with an 'items' key
# returns a list of item objects (events).
      for event in response.get('items', []):
        if( event['summary']=='WakePAC'):
               print( "!!!MATCH!!!   Title query=%s"%event['summary'])
               print( "Debut : ",event['start'])
               print( "Fin : ",event['end'])

               print( 'ALLUMAGE CHAUFFAGE DONE')
               PACStateToSet='1'
               if(event.get('description')):
                 event['description']=event['description']+' \nPAC Started'
               else:
                 event['description']='\nPAC Started'

               updated_event = service.events().update(calendarId=calendarId, eventId=event['id'], body=event).execute()

        if( event['summary']=='OpenShutter'):
               print( "!!!MATCH!!!   Title query=%s"%event['summary'])
               print( "Debut : ",event['start'])
               print( "Fin : ",event['end'])

               OpenAllShutter()

               service.events().delete(calendarId=calendarId, eventId=event['id']).execute()

        if( event['summary']=='CloseShutter'):
               print( "!!!MATCH!!!   Title query=%s"%event['summary'])
               print( "Debut : ",event['start'])
               print( "Fin : ",event['end'])

               CloseAllShutter()

               service.events().delete(calendarId=calendarId, eventId=event['id']).execute()


    # the list_next method.
      request = service.events().list_next(request, response)

# From web browser : http://192.168.0.14/domocan/www/php/CmdPAC.php?state=0
    url='http://localhost/domocan/www/php/CmdPAC.php?state='+PACStateToSet
    print( url)
#OLD    url_response = urllib2.urlopen(url)
    url_response = urllib.request.urlopen(url)
#    print url_response.info()
    html = url_response.read()
# do something
    print( html)
    url_response.close()  # best practice to close the file


def callable_func():
#    os.system("clear") #this is more for my benefit and is in no way necesarry
    print ("------------start-----------")
#OLD    FullTextQuery(calendar_service)
    FullTextQuery()
    print( "-------------end------------")

def OpenAllShutter():
    print( 'Ouverture')

#    if(datetime.isoweekday(datetime.now())<=5): # Only Monday to Friday
    if(datetime.isoweekday(datetime.now())>10): # Never
#    if(datetime.isoweekday(datetime.now())>0): # Always
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x06&entree=0x0E&data0=0x26'  # Volet Salon
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

       time.sleep( 10 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x0B&data0=0x26' # VR Sdb 1
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

       time.sleep( 3 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x06&entree=0x09&data0=0x52' # VR haut cuisine
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

#    if(datetime.isoweekday(datetime.now())<=5): # Only Monday to Friday
    if(datetime.isoweekday(datetime.now())>10): # Never
#    if(datetime.isoweekday(datetime.now())>0): # Always

       time.sleep( 0.5 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x02&entree=0x02&data0=0x26' # Chambre 3
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

       time.sleep( 0.5 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x02&entree=0x07&data0=0x26' # Chambre 4
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

       time.sleep( 0.5 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x04&entree=0x06&data0=0x26' # VR Fixe Chambre Nord
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

       time.sleep( 0.5 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x04&entree=0x01&data0=0x26' # VR FChambre Nord
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

       time.sleep( 0.5 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x04&data0=0x26' # VR Fixe Chambre Terrasse
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

       time.sleep( 0.5 )
       url='http://localhost/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x06&data0=0x26' # VR Chambre Terrasse
       url_response = urllib.urlopen(url)
       html = url_response.read()
       url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x02&entree=0x09&data0=0x26' # VR Sdb 2 + Garage
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file


	   
def CloseAllShutter():

    print('Fermeture Volet')
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x06&entree=0x0F&data0=0x26'  # Volet Salon
    url_response = urllib.urlopen(url)
    #    print url_response.info()
    html = url_response.read()
    # do something
    #               print html
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x02&entree=0x03&data0=0x26' # Chambre 3
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x02&entree=0x06&data0=0x26' # Chambre 4
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x02&entree=0x09&data0=0x52' # VR Sdb 2 + Garage
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x00&data0=0x26' # VR Sdb 1
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x04&entree=0x07&data0=0x26' # VR Fixe Chambre Nord
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x04&entree=0x00&data0=0x26' # VR Chambre Nord
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x03&data0=0x26' # VR Fixe Chambre Terrasse
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x05&data0=0x26' # VR Chambre Terrasse
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file

    time.sleep( 0.5 )
    url='http://localhost/domocan/www/php/CmdVR.php?carte=0x06&entree=0x08&data0=0x52' # VR haut cuisine
    url_response = urllib.urlopen(url)
    html = url_response.read()
    url_response.close()  # best practice to close the file


#callable_func() #if we don't use scheduler, direct call
scheduler = Scheduler(standalone=True)
scheduler.add_interval_job(callable_func,minutes=wake_interval) #minutes=5 or seconds=30
scheduler.start() #runs the program indefinatly on an interval of 15 minutes


# START FROM METAR INFO
# Calcul heure de fin suivant temperature actuelle
#                 station='LFRN'

#                  BASE_URL = "http://weather.noaa.gov/pub/data/observations/metar/stations"
#                  station = station.upper()
#                  url = "%s/%s.TXT" % (BASE_URL, station)
#                  metar_file = urllib.urlopen(url)

               # The file will usually have two lines--one with a date and time, and one
               # with the actual METAR code.
#                  for line in metar_file:
#                    if line.startswith(station):
#                      metar_code = line.strip()
#                  metar_file.close()
#                  if station == '':
#                     station = 'Fuck.'
#                  else:
#                     station = 'AAAAAA'

#                  metar = Metar.Metar(metar_code)

#                  temperature_celsius = int(round(metar.temp.value('C')))
#                  print 'temperature_celsius=',temperature_celsius

#                  dewpoint_celsius = int(round(metar.dewpt.value('C')))
#                  print 'dewpoint_celsius=',dewpoint_celsius
# END METAR INFO
if __name__ == '__main__':
    callable_func()()
