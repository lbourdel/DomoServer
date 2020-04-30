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
# For forecastio
from forecastio import load_forecast
import time

def get_forecast():
    forecastio_api_key = "xxx"
    # Cesson-Sevigne
    lat = 48.0985 
    lng = -1.6039
    #https://api.forecast.io/forecast/xxx/48.0985,-1.6039?lang=fr&units=si

    forecast = load_forecast(forecastio_api_key, lat, lng, units= 'si' )
    return forecast

if __name__ == '__main__':
    forecast_cesson = get_forecast()

    by_hour = forecast_cesson.hourly()

    by_day = forecast_cesson.daily()

    print(by_day.icon)
