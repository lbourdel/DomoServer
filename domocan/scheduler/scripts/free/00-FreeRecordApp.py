import requests
import json

payload = {'app_id': 'fr.freebox.testapp', 'app_name': 'FreePyLBR', 'app_version': '4', 'device_name': 'LBR Script Python'}

payload = json.dumps(payload)
r = requests.post("http://mafreebox.freebox.fr/api/v4/login/authorize/", data=payload)

print( r.text)

# Take track_id from previous answer to replace 3 with correct value
r = requests.get("http://mafreebox.freebox.fr/api/v4/login/authorize/3")
print( r.text)