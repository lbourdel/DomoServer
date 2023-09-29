# Copyright 2018 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

# [START calendar_quickstart]
from __future__ import print_function
import platform
import os.path
import datetime
import pickle
from googleapiclient.discovery import build
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request

if platform.system()=='Linux':
	path_to_api_keys='/home/pi/CalendarGoogle/'
else:
	path_to_api_keys=os.path.dirname(os.path.abspath(__file__))+'\\..\\CalendarAPIKey'
# If modifying these scopes, delete the file token.pickle.
SCOPES = ['https://www.googleapis.com/auth/calendar']

mycalendarId = '4cstbss5a1pi3avvt2qq59a950@group.calendar.google.com'

def init_calendar():
	creds = None
	# The file token.pickle stores the user's access and refresh tokens, and is
	# created automatically when the authorization flow completes for the first
	# time.
	if os.path.exists( os.path.join(path_to_api_keys,'token.pickle')):
		print('token.pickle exists')
		with open( os.path.join(path_to_api_keys,'token.pickle'), 'rb') as token:
			creds = pickle.load(token)
	# If there are no (valid) credentials available, let the user log in.
	if not creds or not creds.valid:
		if creds and creds.expired and creds.refresh_token:
			creds.refresh(Request())
		else:
			flow = InstalledAppFlow.from_client_secrets_file(
				os.path.join(path_to_api_keys,'credentials.json'), SCOPES)
			creds = flow.run_local_server(port=0)
		# Save the credentials for the next run
		with open(os.path.join(path_to_api_keys,'token.pickle'), 'wb') as token:
			pickle.dump(creds, token)

	service = build('calendar', 'v3', credentials=creds)
	return service

def get_events(param_service, start_date, end_date=0, text_search = ""):

	# Call the Calendar API
	if end_date==0:
		print('Getting the upcoming 10 events')
		events_result = param_service.events().list(calendarId=mycalendarId,
											  timeMin=start_date,
											  maxResults=10,
											  singleEvents=True,
											  orderBy='startTime').execute()
	else:
		print('Getting events from',start_date,' to ', end_date)
		events_result = param_service.events().list(calendarId=mycalendarId,
											  timeMin=start_date,
											  timeMax=end_date,
											  singleEvents=True,
											  orderBy='startTime').execute()

	param_events = events_result.get('items', [])

	if text_search:
# using list comprehension to remove duplicated from list
		res = []
		[res.append(x) for x in param_events if text_search in x['summary']]
		param_events = res
		
	if not param_events:
		print('No upcoming events found.')
	return param_events

def search_delete(param_service, text_search, start_date=0):

	# Call the Calendar API
	start_date = (datetime.datetime.utcnow()-datetime.timedelta(hours=24)).isoformat() + 'Z' # 'Z' indicates UTC time
	print('Getting the upcoming 10 events')
	events_result = param_service.events().list(calendarId=mycalendarId,
											timeMin=start_date,
											maxResults=10,
											singleEvents=True,
											orderBy='startTime').execute()

	param_events = events_result.get('items', [])

# using list comprehension to remove duplicated from list
	res = []
	[res.append(x) for x in param_events if text_search  in x['summary']]

	if not res:
		print('No upcoming events found.')
	else:
		[delete_event(param_service, x['id']) for x in res ]


def update_event(param_service, param_eventId, param_event):

	updated_event = param_service.events().update(calendarId=mycalendarId, eventId=param_eventId, body=param_event).execute()
	return updated_event

def insert_event(param_service, param_body):

	updated_event = param_service.events().insert(calendarId=mycalendarId, body=param_body).execute()
	return updated_event

def delete_event(param_service, param_eventId):

	updated_event = param_service.events().delete(calendarId=mycalendarId, eventId=param_eventId).execute()
	return updated_event


def main():
	"""Shows basic usage of the Google Calendar API.
	Prints the start and name of the next 10 events on the user's calendar.
	"""
	creds = init_calendar()

	date_min = (datetime.datetime.utcnow()-datetime.timedelta(hours=10*24)).isoformat() + 'Z' # 'Z' indicates UTC time
	date_max = (datetime.datetime.utcnow()).isoformat() + 'Z' # 'Z' indicates UTC time

	search_delete(creds, "TEMPO_")
	events = get_events(creds, date_min, date_max, "EVSE")

	for event in events:
		start = event['start'].get('dateTime', event['start'].get('date'))
		print(start, event['summary'])


if __name__ == '__main__':
	main()
# [END calendar_quickstart]

