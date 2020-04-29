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
import datetime
import pickle
import os.path
from googleapiclient.discovery import build
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request

path_to_credentials='/home/pi/CalendarGoogle/'

# If modifying these scopes, delete the file token.pickle.
SCOPES = ['https://www.googleapis.com/auth/calendar']

mycalendarId = '4cstbss5a1pi3avvt2qq59a950@group.calendar.google.com'

def init_calendar():
    creds = None
    # The file token.pickle stores the user's access and refresh tokens, and is
    # created automatically when the authorization flow completes for the first
    # time.
    if os.path.exists( os.path.join(path_to_credentials,'token.pickle')):
        print('token.pickle exists')
        with open( os.path.join(path_to_credentials,'token.pickle'), 'rb') as token:
            creds = pickle.load(token)
    # If there are no (valid) credentials available, let the user log in.
    if not creds or not creds.valid:
        if creds and creds.expired and creds.refresh_token:
            creds.refresh(Request())
        else:
            flow = InstalledAppFlow.from_client_secrets_file(
                os.path.join(path_to_credentials,'credentials.json'), SCOPES)
            creds = flow.run_local_server(port=0)
        # Save the credentials for the next run
        with open(os.payh.join(path_to_credentials,'token.pickle'), 'wb') as token:
            pickle.dump(creds, token)
    return creds

def get_events(param_creds, start_date, end_date=0):
    service = build('calendar', 'v3', credentials=param_creds)

    # Call the Calendar API
    if end_date==0:
        print('Getting the upcoming 10 events')
        events_result = service.events().list(calendarId=mycalendarId,
                                              timeMin=start_date,
                                              maxResults=10,
                                              singleEvents=True,
                                              orderBy='startTime').execute()
    else:
        print('Getting events from',start_date,' to ', end_date)
        events_result = service.events().list(calendarId=mycalendarId,
                                              timeMin=start_date,
                                              timeMax=end_date,
                                              singleEvents=True,
                                              orderBy='startTime').execute()

    param_events = events_result.get('items', [])

    if not param_events:
        print('No upcoming events found.')
    return param_events

def update_event(param_creds, param_eventId, param_event):
    service = build('calendar', 'v3', credentials=param_creds)

    updated_event = service.events().update(calendarId=mycalendarId, eventId=param_eventId, body=param_event).execute()
    return updated_event

def insert_event(param_creds, param_body):
    service = build('calendar', 'v3', credentials=param_creds)

    updated_event = service.events().insert(calendarId=mycalendarId, body=param_body).execute()
    return updated_event

def delete_event(param_creds, param_eventId):
    service = build('calendar', 'v3', credentials=param_creds)

    updated_event = service.events().delete(calendarId=mycalendarId, eventId=param_eventId).execute()
    return updated_event


def main():
    """Shows basic usage of the Google Calendar API.
    Prints the start and name of the next 10 events on the user's calendar.
    """
    creds = init_calendar()

    date_min = datetime.datetime.utcnow().isoformat() + 'Z' # 'Z' indicates UTC time
    date_max = (datetime.datetime.utcnow()+datetime.timedelta(hours=12)).isoformat() + 'Z' # 'Z' indicates UTC time

    events = get_events(creds, date_min, date_max)

    for event in events:
        start = event['start'].get('dateTime', event['start'].get('date'))
        print(start, event['summary'])


if __name__ == '__main__':
    main()
# [END calendar_quickstart]

