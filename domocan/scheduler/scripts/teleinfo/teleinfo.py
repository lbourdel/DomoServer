#!/usr/bin/env python3

# nohup python3 teleinfo.py >/dev/null 2>&1 &

import sys, time
import paho.mqtt.client as mqtt

sys.path.append( "/var/www/domocan/scheduler/scripts/calendar" )
from gcalendar import init_calendar, get_events, update_event, insert_event, delete_event
# from feed.date.rfc3339 import timestamp_from_tf
# from feed.date.rfc3339 import tf_from_timestamp #also for the comparator
import pytz

import getopt
import serial
import serial.tools.list_ports
import time
from datetime import datetime, timedelta
import thingspeak

import urllib.request


# def addTempoEventCalendar(couleur):
	# ressource = init_calendar()

	# TZ_OFFSET = pytz.timezone('Europe/Paris').localize(datetime.now()).strftime('%z')

	# startTempo=(datetime.now() + timedelta(days=1)).strftime('%Y-%m-%dT')+'06:00:00'+TZ_OFFSET
	# print(startTempo)
	# print( timestamp_from_tf(startTempo))
	# print( tf_from_timestamp(startTempo))
	# endTempo=timestamp_from_tf(  tf_from_timestamp(startTempo) +15*3600)
	# .replace(tzinfo=pytz.timezone('UTC'))
	# print(endTempo)
	# eventShutter = {
		# 'summary': 'couleur',
		# 'location': 'Cesson-Sevigne, France',
		# 'start': {
			# 'dateTime': startTempo,
	                        # 'timeZone': 'Europe/Paris(
		# },
		# 'end': {
			# 'dateTime':  endTempo, # startOpenShutter+1H
	                       # 'timeZone': 'Europe/Paris'
		# },

	# }

	# created_event = insert_event(ressource, param_body=eventShutter)
	# print(  created_event   )

	
ps_name = sys.argv[0]
argv = sys.argv[1:]

standard = False
baudrate = 1200
port = None

def list_serial_ports():
    ports = serial.tools.list_ports.comports()
    for port, desc, hwid in sorted(ports):
        print("{}: {} [{}]".format(port, desc, hwid))

def find_serial_ports():
    ti = None
    ports = serial.tools.list_ports.comports()
    for port, desc, hwid in sorted(ports):
        if "SER=TINFO" in hwid:
            ti = port
        elif "ttyAMA0" in desc and ti==None:
            ti = port
    return ti

def usage():
    print("{} -p|--port <serial_port>".format(ps_name))
    print("{} -s|--standard".format(ps_name))
    print("{} -l|--list".format(ps_name))
    print("{} [-h|--help]".format(ps_name))
    print("")

def checksum(chaine):
	Controle=0;
	trame = stri.split(' ')[0] + " " + stri.split(' ')[1];
	Checksum = stri.split(' ')[2]
	# print(chaine)
	# print(len(chaine))

	for ch in chaine[:-2]:
		Controle += ord(ch)
	Controle = (Controle & 0x3F) + 0x20           
	# print(Checksum)
	# print(chr(Controle))

	if ( chr(Controle) == Checksum):
		# print('MATCH !!!')
		return True
	else:
		print('DONT MATCH !!!')
		return False


def sendToWhatApp(text):
	return False # Service down payment needed after trial
	text = text.replace(" ","+")
	baseURL = 'https://api.callmebot.com/whatsapp.php?phone=33609863994&text='+text+'&apikey=3011836'
	req = urllib.request.Request(baseURL, headers={'User-Agent': 'Mozilla/5.0'})
	# print(baseURL)
	f = urllib.request.urlopen(req )
	html = f.read()
	# print(html) 
	f.close()


def mqtt_publish(topic, payload):
        mqtt_client.publish(topic, payload=striList[1], qos=0, retain=False)

def on_connect(client, userdata, flags, rc):
    print(f"Connected with result code {rc}")

def on_disconnect(client, userdata, rc):
    print("Unexpected disconnection  {rc}")
    if rc != 0:
        print("Unexpected disconnection.")

# Main program start here
# ========================
try:
    opts, args = getopt.getopt(argv, "hlsp:", ["help", "list", "standard", "port="])
except getopt.GetoptError:
    usage()
    sys.exit(2)
for opt, arg in opts:
    if opt in ("-h", "--help"):
        usage()
        sys.exit()
    elif opt in ("-l", "--list"):
        list_serial_ports()
        sys.exit()
    elif opt in ("-p", "--port"):
        port = arg
    elif opt in ("-s", "standard"):
        standard = True
        mode = "Standard"
        baudrate = 9600
if port is None:
    print("Missing serial port argument trying to find Teleinfo")
    port = find_serial_ports()
    if port is None:
        print("Unable to find serial port for Teleinfo please use one of the following")
        list_serial_ports()
        usage()
        sys.exit(2)

if standard == True:
	mode = "Standard"
else:
	mode = "Historique"

print("Teleinfo : Mode {}".format(mode))
print("Port     : {}".format(port))
print("Vitesse  : {}".format(baudrate))
print("\r\n")

try:
	tinfo = serial.Serial( port=port,
						   baudrate=baudrate,
						   parity=serial.PARITY_EVEN,
						   stopbits=serial.STOPBITS_ONE,
						   bytesize=serial.SEVENBITS )
except:
	print("Port COM busy : Serial ")
	exit()


stri = ''
counterIINST = 0
counterPAPP = 0
counterMax=10
courantImean = 0
puissancePmean = 0
channel = thingspeak.Channel(id=2015860, api_key='Z5YSPQP20HU3IPD7')
StoreToCloud = False
tarif_tempo = ''
periode_tarif = ''
type_abo = ''

compteur_JBleu_HC = ''
compteur_JBleu_HP = ''
compteur_JBlanc_HC = ''
compteur_JBlanc_HP = ''
compteur_JRouge_HC = ''
compteur_JRouge_HP = ''
compteur_JBleu_HC_old = ''
compteur_JBleu_HP_old = ''
compteur_JBlanc_HC_old = ''
compteur_JBlanc_HP_old = ''
compteur_JRouge_HC_old = ''
compteur_JRouge_HP_old = ''
# abonnement 9kVA 14.82€ TTC 15.33 +3.44%
tarif_JBleu_HC = 0.097 # 0.0862		# + 2,56 %
tarif_JBleu_HP = 0.1249 # 0.1272		# - 8,73 %
tarif_JBlanc_HC = 0.114 # 0.1112		# + 12,49 %
tarif_JBlanc_HP = 0.1508 # 0.1653		# - 1,82 %
tarif_JRouge_HC = 0.1216 # 0.1222		# - 0,56 %
tarif_JRouge_HP = 0.6712 # 0.5486		# + 22,35 %

mqtt_client = mqtt.Client()
mqtt_client.on_connect = on_connect
mqtt_client.on_connect = on_disconnect
print('mqtt connection ')
mqtt_client.connect("localhost", port=1883, keepalive=300)


while True:
	try:
		c = tinfo.read(1)
	except:
		print("Port COM busy : read")
		exit()

	# print(c);
	# compteur_JBleu_HC_Old = compteur_JBleu_HC
	if c.decode('ascii')=='\n':
		stri=''
	elif c.decode('ascii')=='\r':
		striList = stri.split(' ')
		# print(len(striList))
		# print(stri)
		# print(striList)
		if len(striList)>2:
			# if( checksum(stri)==True):
				if stri.startswith("PTEC"):
					if( periode_tarif != striList[1]):
						periode_tarif = striList[1]
						# msg = 'Periode Tarifaire '+ striList[0] + '= ' + striList[1] + ' at ' + str(datetime.now())
						# sendToWhatApp(msg)
						if( "HCJB" in striList[1]):
							sendToWhatApp('Chgt Periode Conso '+ striList[1] + '= ' + compteur_JBleu_HC + 'old:' + compteur_JBleu_HC_old)
							compteur_JBleu_HC_Old = compteur_JBleu_HC
						if( "HPJB" in striList[1]):
							sendToWhatApp('Chgt Periode Conso '+ striList[1] + '= ' + compteur_JBleu_HP + ' old:' + compteur_JBleu_HP_old)
							compteur_JBleu_HP_old = compteur_JBleu_HP
						if( "HCJW" in striList[1]):
							sendToWhatApp('Chgt Periode Conso '+ striList[1] + '= ' + compteur_JBlanc_HC + ' old:' + compteur_JBlanc_HC_old)
							compteur_JBlanc_HC_old = compteur_JBlanc_HC
						if( "HPJW" in striList[1]):
							sendToWhatApp('Chgt Periode Conso '+ striList[1] + '= ' + compteur_JBlanc_HP + ' old:' + compteur_JBlanc_HP_old)
							compteur_JBlanc_HP_old = compteur_JBlanc_HP
						if( "HCJR" in striList[1]):
							sendToWhatApp('Chgt Periode Conso '+ striList[1] + '= ' + compteur_JRouge_HC + ' old:' + compteur_JRouge_HC_old)
							compteur_JRouge_HC_old = compteur_JRouge_HC
						if( "HPJR" in striList[1]):
							sendToWhatApp('Chgt Periode Conso '+ striList[1] + '= ' + compteur_JRouge_HP + ' old:' + compteur_JRouge_HP_old)
							compteur_JRouge_HP_old = compteur_JRouge_HP
						
				if stri.startswith("BBRHCJB"):
					compteur_JBleu_HC = striList[1]

				if stri.startswith("BBRHPJB"):
					compteur_JBleu_HP = striList[1]

				if stri.startswith("BBRHCJW"):
					compteur_JBlanc_HC = striList[1]

				if stri.startswith("BBRHPJW"):
					compteur_JBlanc_HP = striList[1]

				if stri.startswith("BBRHCJR"):
					compteur_JRouge_HC = striList[1]

				if stri.startswith("BBRHPJR"):
					compteur_JRouge_HP = striList[1]			
				
				# if stri.startswith("OPTARIF"):
					# if( type_abo != striList[1]):
						# type_abo = striList[1]
						# msg = 'Type Abonnement '+ striList[0] + '= ' + striList[1] + ' at ' + str(datetime.now())
						# sendToWhatApp(msg)
				
				if stri.startswith("DEMAIN"):
					if( tarif_tempo != striList[1]):
						tarif_tempo = striList[1]
						msg = 'Couleur '+ striList[0] + '= ' + striList[1]
						if not '----' in striList[1]:
							sendToWhatApp(msg)
						# if 'ROUG' in striList[1]:
							# addTempoEventCalendar(striList[1])
						# if 'BLAN' in striList[1]:
							# addTempoEventCalendar(striList[1])
				
				if stri.startswith("ADPS"):
					# print(striList[ : -1])
					courantI = striList[1]
					msg = 'OVER CURRENT '+striList[0] + '=' +striList[1] 
					mqtt_publish('linky/IAlert', payload=striList[1])
					sendToWhatApp(msg)

				if stri.startswith("IINST"):
					# print(striList[ : -1])
					courantI = striList[1]
					courantImean += int(courantI)
					counterIINST+=1
					# print( counterIINST )
					mqtt_publish('linky/Irms', payload=courantI)
					if counterIINST >= counterMax:
						courantImean/=counterIINST
						print('======>',courantImean)
						counterIINST=0
					# print( courantI )
					# print( courantImean )

				if stri.startswith("PAPP"):
					# print(striList[ : -1])
					puissanceP = striList[1]
					puissancePmean += int(puissanceP)

					counterPAPP+=1
					print( counterPAPP )
					mqtt_publish('linky/Papp', payload=(puissanceP))
					if counterPAPP >= counterMax:
						puissancePmean/=counterPAPP
						print('======>',puissancePmean)
						counterPAPP=0
						StoreToCloud = True
						# print( puissanceP )

		if StoreToCloud==True:
			StoreToCloud=False
			try:
				# baseURL = 'https://api.thingspeak.com/update?api_key=B9YWLL85191R9CGR&field2='+str(puissancePmean)
				# req = urllib.request.Request(baseURL, headers={'User-Agent': 'Mozilla/5.0'})
				# print(baseURL)
				# f = urllib.request.urlopen(req )
				# html = f.read()
				# print(html) 
				# f.close()
				# response = channel.update({'field1': courantImean, 'field2': puissancePmean})
				response = channel.update({'field1': courantImean})
			except:
				print('tata',response)

			courantImean=0
			puissancePmean=0
			# print(compteur_JBleu_HC, compteur_JBleu_HP, compteur_JBlanc_HC, compteur_JBlanc_HP, compteur_JRouge_HC, compteur_JRouge_HP)


	else:
		stri = stri + c.decode('ascii')
	#sys.stdout.write( str(c) )
    
tinfo.close()

# ADCO 031662531488
# OPTARIF HC
# ISOUSC 45
# HCHC 032787201
# HCHP 033535804
# PTEC HP..
# IINST 007
# IMAX 090
# PAPP 01600
# HHPHC A
# MOTDETAT 000000


# ADCO 031662531488 F
# OPTARIF BBR( S
# ISOUSC 45 ?
# BBRHCJB 032918316 >
# BBRHPJB 033657115 I
# BBRHCJW 000000000 2
# BBRHPJW 000000000 ?
# BBRHCJR 000000000 -
# BBRHPJR 000000000 :
# PTEC HPJB P
# DEMAIN ---- "
# IINST 005 \
# IMAX 090 H
# PAPP 01160 )
# HHPHC A ,
# MOTDETAT 000000 B



# Trame recu par la teleinfo      (Expliquations ! non recu par la teleinfo)

# ADCO 040422040644 5	        (N° d’identification du compteur : ADCO (12 caractères))
# OPTARIF HC.. <	                (Option tarifaire (type d’abonnement) : OPTARIF (4 car.))
# ISOUSC 45 ?	                (Intensité souscrite : ISOUSC ( 2 car. unité = ampères))
# HCHC 077089461 0	        (Index heures creuses si option = heures creuses : HCHC ( 9 car. unité = Wh))
# HCHP 096066754 >	        (Index heures pleines si option = heures creuses : HCHP ( 9 car. unité = Wh))

# BBRHCJB Index Heures Creuses Jours Bleus ( 9 car. unité = Wh))
# BBRHPJB Index Heures Pleines Jours Bleus ( 9 car. unité = Wh))
# BBRHCJW Index Heures Creuses Jours Blancs ( 9 car. unité = Wh))
# BBRHPJW Index Heures Pleines Jours Blancs ( 9 car. unité = Wh))
# BBRHCJR Index Heures Creuses Jours Rouges ( 9 car. unité = Wh))
# BBRHPJR Index Heures Pleines Jours Rouges ( 9 car. unité = Wh))
# PTEC HP..  	                (Période tarifaire en cours : PTEC ( 4 car.))
# DEMAIN Couleur du lendemain ( 4 car.)
# IINST 002 Y	                (Intensité instantanée : IINST ( 3 car. unité = ampères))
# ADPS Avertissement de Dépassement De Puissance Souscrite ( 3 car. unité = ampères)
# IMAX 044 G	                (Intensité maximale : IMAX ( 3 car. unité = ampères))
# PAPP 00460 +	                (Puissance apparente : PAPP ( 5 car. unité = Volt.ampères))
# HHPHC E 0	                (Groupe horaire si option = heures creuses ou tempo : HHPHC (1 car.))
# MOTDETAT 000000 B	        (Mot d’état (autocontrôle) : MOTDETAT (6 car.))

# Chaque ligne de donnée est présentée ainsi :

# LF (0x0A) \n
# ETIQUETTE (4 à 8 caractères)
# SP (0x20)
# DATA (1 à 12 caractères)
# SP (0x20)
# CHECKSUM (caractère de contrôle : Somme de caractère)
# CR (0x0D) \r

# Le calcul de la checksum est la suivante:

# Elle est calculée sur l’ensemble des caractères allant du début du champ étiquette à la fin du champ donnée, caractère Espace inclus. On fait tout d’abord la somme des codes ASCII de tous ces caractères. Pour éviter d’introduire des fonctions ASCII (00 à 1F en hexadécimal), on ne conserve que les six bits de poids faible du résultat obtenu (cette opération se traduit par un ET logique entre la somme précédemment calculée et 3Fh). Enfin, on ajoute 20h en hexadécimal. Le résultat sera donc toujours un caractère allant de 20 à 5F en hexadécimal.