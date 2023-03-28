# Example Usage of TinyTuya
# https://github.com/jasonacox/tinytuya
# Install TinyTuya
# python -m pip install tinytuya
# python -m tinytuya scan

# https://iot.tuya.com/cloud
# Project Code: p1676227679889mvmpfr

# {
    # "apiKey": "pcu53w98pp7ddfhm34ew",
    # "apiSecret": "c87b771a68b84e68a282654dcdf8573c",
    # "apiRegion": "eu",
    # "apiDeviceID": "00822444600194feee58"
# }


# Device Listing

# [
    # {
        # "name": "WiFi switch-4CH",
        # "id": "00822444600194feee58",
        # "key": "d37f3c1f34c28ca2",
        # "mac": "60:01:94:fe:ee:58",
        # "category": "kg",
        # "product_name": "WiFi switch-4CH",
        # "product_id": "waq2wj9pjadcg1qc",
        # "biz_type": 0,
        # "model": "TY-DIY-S04",
        # "sub": false,
        # "icon": "https://images.tuyaeu.com/smart/icon/ay1525015673758Ghvpg/2587b975a6b0645e83559835ef7c7eef.png",      
        # "uuid": "00822444600194feee58"
    # }
# ]
# python -m tinytuya wizard
# TinyTuya Setup Wizard [1.10.2]

#     Existing settings:
#         API Key=pcu53w98pp7ddfhm34ew 
#         Secret=c87b771a68b84e68a282654dcdf8573c
#         DeviceID=00822444600194feee58
#         Region=eu

#     Use existing credentials (Y/n): y


# Device Listing

# [
#     {
#         "name": "WiFi switch-4CH",   
#         "id": "00822444600194feee58",
#         "key": "d37f3c1f34c28ca2",   
#         "mac": "60:01:94:fe:ee:58",
#         "category": "kg",
#         "product_name": "WiFi switch-4CH",
#         "product_id": "waq2wj9pjadcg1qc",
#         "biz_type": 0,
#         "model": "TY-DIY-S04",
#         "sub": false,
#         "icon": "https://images.tuyaeu.com/smart/icon/ay1525015673758Ghvpg/2587b975a6b0645e83559835ef7c7eef.png",
#         "uuid": "00822444600194feee58"
#     }
# ]

# >> Saving list to devices.json
#     1 registered devices saved

# >> Saving raw TuyaPlatform response to tuya-raw.json

import tinytuya
import time

devices = tinytuya.deviceScan()
for device in devices:
    print(device)
    print(devices[device])

time.sleep(5)

d = tinytuya.OutletDevice('00822444600194feee58', '192.168.1.37', 'd37f3c1f34c28ca2')
d.set_version(3.3)
data = d.status() 


print('Device status: %r' % data)

d.turn_on(switch=4)

time.sleep(5)

data = d.status() 
print('Device status: %r' % data)


d.turn_off(switch=4)
time.sleep(5)
data = d.status() 
print('Device status: %r' % data)

