import sys
sys.path.append('./libpyfoscam/')
from libpyfoscam import FoscamCamera
import pprint

def print_ipinfo(returncode, params):
    if returncode != 0:
        print ('Failed to get IPInfo!')
        return
    print ('IP: %s, Mask: %s' % (params['ip'], params['mask']))

def config_motionscheduler():
	mycam = FoscamCamera('192.168.1.15', 88, 'admin', 'jill2006', daemon=False)
	mycam.get_ip_info(print_ipinfo)

	ret_motion_detect_config1 = mycam.get_motion_detect_config1()
	pprint.pprint(ret_motion_detect_config1[1])

	# 1 bit by 30mn : 00:00 to OO:30 ON = 800000000000h
	# 1 bit by 30mn : 23:30 to OO:00 ON = 000000000001h
	# 'schedule0' : Monday
	ret_motion_detect_config1[1]['schedule3']= '7'

	mycam.set_motion_detect_config1(ret_motion_detect_config1[1])

	# ret = mycam.get_schedule_record_config()
	# pprint.pprint(ret )

if __name__ == '__main__':
	config_motionscheduler()


