import sys
sys.path.append('./libpyfoscam/')
from libpyfoscam import FoscamCamera
import pprint
import ssl
import urllib.request
import urllib

def print_ipinfo(returncode, params):
    pprint.pprint(returncode)
    pprint.pprint(params)
    # if returncode != 0:
        # print ('Failed to get IPInfo!')
        # return
    print ('IP: %s, Mask: %s' % (params['ip'], params['mask']))

def SetPACState():
	url='http://192.168.1.122:88/cgi-bin/CGIProxy.fcgi?usr=admin&pwd=jill2006&cmd=getIPInfo'
	url_response = urllib.request.urlopen(url,timeout=10)
	html = url_response.read()
	print(html)
	url_response.close()  # best practice to close the file


		
def config_motionscheduler():
	mycam = FoscamCamera('192.168.1.122', 88, 'admin', 'jill2006', daemon=False)
	mycam.get_ip_info( print_ipinfo )
	

	# mycam.get_port_info()
	# mycam.refresh_wifi_list()
	# pprint.pprint(rc)
	# pprint.pprint(info)

	# ret_motion_detect_config1 = mycam.get_motion_detect_config1()
	# pprint.pprint(ret_motion_detect_config1[1])

	# 1 bit by 30mn : 00:00 to OO:30 ON = 800000000000h
	# 1 bit by 30mn : 23:30 to OO:00 ON = 000000000001h
	# 'schedule0' : Monday
	# ret_motion_detect_config1[1]['schedule3']= '7'

	# mycam.set_motion_detect_config1(ret_motion_detect_config1[1])

	# ret = mycam.get_schedule_record_config()
	# pprint.pprint(ret )

if __name__ == '__main__':
	# SetPACState()
	# config_motionscheduler()
	
	import urllib.parse
	import urllib.request

	url = 'http://192.168.1.122:88/cgi-bin/CGIProxy.fcgi'
	values = {'usr' : 'admin',
			  'pwd' : 'jill2006',
			  'cmd' : 'getIPInfo' }

	data = urllib.parse.urlencode(values)
	data = data.encode("utf-8") # data should be bytes
	print(data)
	req = urllib.request.Request(url, data)
	with urllib.request.urlopen(req) as response:
	   the_page = response.read()


