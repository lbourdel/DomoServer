import requests

def get_forecast():

	url = 'https://api.open-meteo.com/v1/forecast?latitude=48.12&longitude=-1.60&hourly=temperature_2m,relativehumidity_2m,cloudcover,cloudcover_low,cloudcover_mid,cloudcover_high&daily=sunrise,sunset&forecast_days=2&timeformat=unixtime&timezone=Europe%2FParis'

	res = requests.get(url)
	data = res.json()
	return data