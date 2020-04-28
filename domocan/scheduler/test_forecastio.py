import forecastio



def main():
    api_key = "465485026097be2ee6c9bf09ae9e6020"
    lat = -31.967819
    lng = 115.87718
    forecast = forecastio.load_forecast(api_key, lat, lng)


if __name__ == '__main__':
    main()