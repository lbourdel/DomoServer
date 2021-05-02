import requests
import json

# Lancer l'authorization puis valider sur l'afficheur du serveur
# Modifier les droits d'acces ds l'inface admin

# Attention a ne pas avoir de antislash \ dans le token car pb avec 
# {"success":true,"result":{"app_token":"Rv7udEUk2UDT0wKu+E2PxZcEZQGNE3sJxYfGe2IUO5+vqtV+J6UoQsBgbhD5PR0L","track_id":1}}

# {"success":true,"result":{"status":"granted","challenge":"bWJfGdCTh5+onbz0UIArjxbzWDZcVhQY","password_salt":"hZBntmbZ\/WE7UzwfYeoU7aEgquerOG6W"}}


if False:
	payload = {'app_id': 'fr.freebox.python', 'app_name': 'FreePythonByLBR', 'app_version': '8', 'device_name': 'LBR Script Python'}

	payload = json.dumps(payload)
	r = requests.post("http://mafreebox.freebox.fr/api/v8/login/authorize/", data=payload)

	print( r.text)
else:
	# Take track_id from previous answer to replace 2 with correct value
	r = requests.get("http://mafreebox.freebox.fr/api/v8/login/authorize/1")
	print( r.text)