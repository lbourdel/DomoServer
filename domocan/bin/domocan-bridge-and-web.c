/*  DOMOCAN linux Bridge by Henry2.0
                     With Special thanks to Stefan Krauss and the Socketcan team
*/

#include <stdio.h>
#include <stdlib.h>
#include <libgen.h>
#include <unistd.h>
#include <string.h>
#include <signal.h>
#include <errno.h>

#include <netdb.h>            // struct addrinfo
#include <sys/types.h>
#include <sys/wait.h>
#include <sys/socket.h>
#include <sys/ioctl.h>
#include <sys/time.h>
#include <sys/resource.h>
#include <net/if.h>
#include <netinet/in.h>
#include <netinet/ip.h>       // struct ip and IP_MAXPACKET (which is 65535)
#include <netinet/udp.h>      // struct udphdr
#include <arpa/inet.h>        // inet_pton() and inet_ntop()
#include <bits/ioctls.h>      // defines values for argument "request" of ioctl.

#include <linux/can.h>
#include  "./domocan-libs.h"

// Define some constants.
#define IP4_HDRLEN 20         // IPv4 header length
#define UDP_HDRLEN  8         // UDP header length, excludes data
unsigned char udpframe[32];
  
// Function prototypes
unsigned short int checksum (unsigned short int *, int);
unsigned short int udp4_checksum (struct ip, struct udphdr, unsigned char *, int);

// Config variables
const char interface[]        = "eth0"; // Interface to send packet through.
char destination_ip[16], source_ip[16], Domocan_UDP_Int[]="eth0";


void Signal_Handler(sig) /* signal handler function */
{
        switch(sig) {
                case SIGHUP:
                        /* rehash the server */
                        break;
                case SIGTERM:
                        /* finalize the server */
                        exit(0);
                        break;
        }
}


void print_usage(char *prg) {
        fprintf(stderr, "\nUsage: %s -l <port> -d <port> -i <can interface> - e <eth interface>\n", prg);
        fprintf(stderr, "   Version 0.1\n");
        fprintf(stderr, "\n");
        fprintf(stderr, "         -l <port>           listening UDP port for the server - default 15731\n");
        fprintf(stderr, "         -d <port>           destination UDP port for the server - default 15730\n");
        fprintf(stderr, "         -b <broadcast_addr> broadcast address - default 192.168.0.255\n");
        fprintf(stderr, "         -i <can int>        can interface - default can0\n");
		fprintf(stderr, "         -e <Eth int>        Ethernet interface - default eth0\n");
        fprintf(stderr, "         -f                  running in foreground\n");
        fprintf(stderr, "\n");

}



int main(int argc, char **argv) {
  setpriority(PRIO_PROCESS, 0, -20);
  pid_t pid;
  extern int optind, opterr, optopt;
  int opt;
  struct can_frame frame;

  int status, datalen, sd, *ip_flags;
  //const int on = 1;
  char *interface, *target, *src_ip, *dst_ip;
  struct ip iphdr;
  struct udphdr udphdr;
  unsigned char *data, *packet;
  struct addrinfo hints, *res;
  struct sockaddr_in *ipv4, sin;
  struct ifreq ifr;
  void *tmp;

  
  int sa, sc; // UDP socket , CAN socket
  struct sockaddr_in saddr;
  struct sockaddr_can caddr;
  struct ifreq CAN_ifr;
  socklen_t caddrlen = sizeof(caddr);

  fd_set readfds;

  int i, s, nbytes, ret;

  int local_port = 1470;
  int destination_port = 1470;
  // // int broadcast_address;
  int verbose = 0;
  int background = 1;
  int canid = 0;
  const int on = 1;
  strcpy(CAN_ifr.ifr_name, "can0");

  char bufferHexa[40];
  int udp_bytes, raw_len, CAN_err, DCcom;
  char calc_FCS[2], UDP_CMD[2], PCID[2], Nbytes[2], FCS[2], RX_UDP_Frame[33], Data[25];
  char SID[5], EID[5];
  char bin_val[32], bin_str[32], DomoCANframe[32];
  char temp_msg[64], fcs[2], msg_out[40], tmp_string[40], frm_content[80], buf[10];
  char *hex_byte, hex_val[32], HexBuffer[32];
  char cmd[255];

  // Check modified Process arguments  
  while ((opt = getopt(argc, argv, "l:d:b:i:e:fv?")) != -1) {
    switch (opt) {
                case 'l':
                        local_port = strtoul(optarg, (char **)NULL, 10);
                        break;
                case 'd':
                        destination_port = strtoul(optarg, (char **)NULL, 10);
                        break;
                case 'b':
                        s = inet_pton(AF_INET,optarg,buf);
                        if (s <= 0) {
                                if (s == 0)  {
                                        fprintf(stderr, "Not in presentation format");
                                } else {
                                        perror("inet_pton");
                                }
                                exit(1);
                        }
                        break;
                case 'i':
                        strcpy(CAN_ifr.ifr_name, optarg);
                        break;

				case 'e':
                        strcpy(interface, optarg);
                        break;

                case 'v':
                        verbose = 1;
                        break;
                case 'f':
                        background = 0;
                        break;

                case '?':
                        print_usage(basename(argv[0]));
                        exit(0);
                        break;

                default:
                        fprintf(stderr, "Unknown option %c\n", opt);
                        print_usage(basename(argv[0]));
                        exit(1);
                        break;
    } // END SWITCH
  } // END WHILE

 // Allocate memory for various arrays.
  // Maximum UDP payload size = 65535 - IPv4 header (20 bytes) - UDP header (8 bytes)
  tmp = (unsigned char *) malloc ((IP_MAXPACKET - IP4_HDRLEN - UDP_HDRLEN) * sizeof (unsigned char));
  if (tmp != NULL) {
    data = tmp;
  } else {
    fprintf (stderr, "ERROR: Cannot allocate memory for array 'data'.\n");
    exit (EXIT_FAILURE);
  }
  memset (data, 0, (IP_MAXPACKET - IP4_HDRLEN - UDP_HDRLEN) * sizeof (unsigned char));

  tmp = (unsigned char *) malloc (IP_MAXPACKET * sizeof (unsigned char));
  if (tmp != NULL) {
    packet = tmp;
  } else {
    fprintf (stderr, "ERROR: Cannot allocate memory for array 'packet'.\n");
    exit (EXIT_FAILURE);
  }
  memset (packet, 0, IP_MAXPACKET * sizeof (unsigned char));

  tmp = (char *) malloc (40 * sizeof (char));
  if (tmp != NULL) {
    interface = tmp;
  } else {
    fprintf (stderr, "ERROR: Cannot allocate memory for array 'interface'.\n");
    exit (EXIT_FAILURE);
  }
  memset (interface, 0, 40 * sizeof (char));

  tmp = (char *) malloc (40 * sizeof (char));
  if (tmp != NULL) {
    target = tmp;
  } else {
    fprintf (stderr, "ERROR: Cannot allocate memory for array 'target'.\n");
    exit (EXIT_FAILURE);
  }
  memset (target, 0, 40 * sizeof (char));

  tmp = (char *) malloc (16 * sizeof (char));
  if (tmp != NULL) {
    src_ip = tmp;
  } else {
    fprintf (stderr, "ERROR: Cannot allocate memory for array 'src_ip'.\n");
    exit (EXIT_FAILURE);
  }
  memset (src_ip, 0, 16 * sizeof (char));

    tmp = (char *) malloc (16 * sizeof (char));
  if (tmp != NULL) {
    dst_ip = tmp;
  } else {
    fprintf (stderr, "ERROR: Cannot allocate memory for array 'dst_ip'.\n");
    exit (EXIT_FAILURE);
  }
  memset (dst_ip, 0, 16 * sizeof (char));

  tmp = (int *) malloc (4 * sizeof (int));
  if (tmp != NULL) {
    ip_flags = tmp;
  } else {
    fprintf (stderr, "ERROR: Cannot allocate memory for array 'ip_flags'.\n");
    exit (EXIT_FAILURE);
  }
  memset (ip_flags, 0, 4 * sizeof (int));

// Interface to send packet through.
//  strcpy (interface, "eth0");
  strcpy (interface, Domocan_UDP_Int);
  get_IP_conf(interface, source_ip, destination_ip);
  if (verbose) printf("\nInt: %s, IP Source: %s, Dest: %s\n", interface, source_ip, destination_ip);
  
// Submit request for a socket descriptor to lookup interface.
  if ((sd = socket (AF_INET, SOCK_RAW, IPPROTO_RAW)) < 0) {
    perror ("socket() failed to get socket descriptor for using ioctl() ");
    exit (EXIT_FAILURE);
  }

// Use ioctl() to lookup interface.
  memset (&ifr, 0, sizeof (ifr));
  snprintf (ifr.ifr_name, sizeof (ifr.ifr_name), "%s", interface);
  if (ioctl (sd, SIOCGIFINDEX, &ifr) < 0) {
    perror ("ioctl() failed to find interface ");
    return (EXIT_FAILURE);
  }
  close (sd);
  if (verbose) printf ("Index for interface %s is %i\n", interface, ifr.ifr_ifindex);

// Source IPv4 address: you need to fill this out
  strcpy (src_ip, source_ip);

// Destination URL or IPv4 address
  strcpy (target, destination_ip);

// Fill out hints for getaddrinfo().
  memset (&hints, 0, sizeof (struct addrinfo));
  hints.ai_family = AF_INET;
  hints.ai_socktype = SOCK_STREAM;
  hints.ai_flags = hints.ai_flags | AI_CANONNAME;

  // Resolve target using getaddrinfo().
  if ((status = getaddrinfo (target, NULL, &hints, &res)) != 0) {
    fprintf (stderr, "getaddrinfo() failed: %s\n", gai_strerror (status));
    exit (EXIT_FAILURE);
  }
  ipv4 = (struct sockaddr_in *) res->ai_addr;
  tmp = &(ipv4->sin_addr);
  inet_ntop (AF_INET, tmp, dst_ip, 16);
  freeaddrinfo (res);

  // UDP data
  datalen = 16;

  // IPv4 header
  // IPv4 header length (4 bits): Number of 32-bit words in header = 5
  iphdr.ip_hl = IP4_HDRLEN / sizeof (unsigned long int);
  // Internet Protocol version (4 bits): IPv4
  iphdr.ip_v = 4;
  // Type of service (8 bits)
  iphdr.ip_tos = 0;
  // Total length of datagram (16 bits): IP header + UDP header + datalen
  iphdr.ip_len = htons (IP4_HDRLEN + UDP_HDRLEN + datalen);
  // ID sequence number (16 bits): unused, since single datagram
  iphdr.ip_id = htons (0);
  // Flags, and Fragmentation offset (3, 13 bits): 0 since single datagram
  // Zero (1 bit)
  ip_flags[0] = 0;
  // Do not fragment flag (1 bit)
  ip_flags[1] = 0;
  // More fragments following flag (1 bit)
  ip_flags[2] = 0;
  // Fragmentation offset (13 bits)
  ip_flags[3] = 0;
  iphdr.ip_off = htons ((ip_flags[0] << 15)
                      + (ip_flags[1] << 14)
                      + (ip_flags[2] << 13)
                      +  ip_flags[3]);
  // Time-to-Live (8 bits): default to maximum value
  iphdr.ip_ttl = 255;
  // Transport layer protocol (8 bits): 17 for UDP
  iphdr.ip_p = IPPROTO_UDP;
  // Source IPv4 address (32 bits)
  inet_pton (AF_INET, src_ip, &(iphdr.ip_src));
  // Destination IPv4 address (32 bits)
  inet_pton (AF_INET, dst_ip, &iphdr.ip_dst);
  // IPv4 header checksum (16 bits): set to 0 when calculating checksum
  iphdr.ip_sum = 0;
  iphdr.ip_sum = checksum ((unsigned short int *) &iphdr, IP4_HDRLEN);

  // UDP header
  // Source port number (16 bits): pick a number
  udphdr.source = htons (1470);
  // Destination port number (16 bits): pick a number
  udphdr.dest = htons (1470);
  // Length of UDP datagram (16 bits): UDP header + UDP data
  udphdr.len = htons (UDP_HDRLEN + datalen);
  // UDP checksum (16 bits)
  udphdr.check = udp4_checksum (iphdr, udphdr, data, datalen);
  // The kernel is going to prepare layer 2 information (ethernet frame header) for us.
  // For that, we need to specify a destination for the kernel in order for it
  // to decide where to send the raw datagram. We fill in a struct in_addr with
  // the desired destination IP address, and pass this structure to the sendto() function.
  memset (&sin, 0, sizeof (struct sockaddr_in));
  sin.sin_family = AF_INET;
  sin.sin_addr.s_addr = iphdr.ip_dst.s_addr;

  // Submit request for a raw socket descriptor.
  if ((sd = socket (AF_INET, SOCK_RAW, IPPROTO_RAW)) < 0) {
    perror ("socket() failed ");
    exit (EXIT_FAILURE);
  } // END IF
  // Set flag so socket expects us to provide IPv4 header.
  if (setsockopt (sd, IPPROTO_IP, IP_HDRINCL, &on, sizeof (on)) < 0) {
    perror ("setsockopt() failed to set IP_HDRINCL ");
    exit (EXIT_FAILURE);
  } // END IF
  // Bind socket to interface index.
  if (setsockopt (sd, SOL_SOCKET, SO_BINDTODEVICE, &ifr, sizeof (ifr)) < 0) {
    perror ("setsockopt() failed to bind to interface ");
    exit (EXIT_FAILURE);
  } // END IF
  // Enable Broadcast
  if (setsockopt (sd, SOL_SOCKET, SO_BROADCAST,&ifr,sizeof(ifr)) < 0) {
    perror ("setsockopt() failed to enable Broadcast ");
    exit (EXIT_FAILURE);
  } // END IF
  
  //  if((sa = socket(PF_INET, SOCK_DGRAM, 0)) < 0) {
  if((sa = socket(AF_INET, SOCK_DGRAM, 0)) < 0) {
    perror("inetsocket");
    exit(1);
  } // END IF

  //  SOCKADDR_IN saddr = { 0 };
  saddr.sin_family = AF_INET;
  saddr.sin_addr.s_addr = htonl(INADDR_ANY);
  saddr.sin_port = htons(local_port);

  while(bind(sa,(struct sockaddr*)&saddr, sizeof(saddr)) < 0) {
    printf(".");
    fflush(NULL);
    usleep(100000);
  } // END WHILE

  // CAN Interface Init
  if ((sc = socket(PF_CAN, SOCK_RAW, CAN_RAW)) < 0) {
    perror("socket");
    exit(1);
  } // END IF

  caddr.can_family = AF_CAN;
  caddr.can_ifindex = 0; // bind to all interfaces
  if (ioctl(sc, SIOCGIFINDEX, &CAN_ifr) < 0) {
    perror("SIOCGIFINDEX");
    exit(1);
  } // END IF
  caddr.can_ifindex = CAN_ifr.ifr_ifindex;

  if (bind(sc, (struct sockaddr *)&caddr, caddrlen) < 0) {
    perror("CAN bind");
    exit(1);
  } // END IF

  if (background) {
    // Fork off the parent process
    pid = fork();
    if (pid < 0) {
      exit(EXIT_FAILURE);
    } // END IF
    // If we got a good PID, then we can exit the parent process.
    if (pid > 0) {
      printf("Going into background ...\n");
      exit(EXIT_SUCCESS);
    } // END IF pid
  } // END IF background

  while (1) {

    // I/O Interup Disptach
    FD_ZERO(&readfds); // zero out the read set
    FD_SET(sc, &readfds); // add CAN socket to the read set
    FD_SET(sa, &readfds); // add UDP Serveur socket to the read set

    ret = select((sc > sa)?sc+1:sa+1, &readfds, NULL, NULL, NULL);

    //////////////////////////
    // received a CAN frame //
    //////////////////////////

    if (FD_ISSET(sc, &readfds)) {
      if ((nbytes = read(sc, &frame, sizeof(struct can_frame))) != 0) {
        // CAN frame IN
        sprintf(frm_content, "", "");
        for (i = 0; i < frame.can_dlc; i++) {
          sprintf(buf, "%02X",frame.data[i]);
          strcat(frm_content, buf);
        } // END FOR
        // printf(" (%s)\n", frm_content);
        if (verbose) printf("\n\n>RX CAN: header=%8X data=%s dlc=%d", frame.can_id, frm_content, frame.can_dlc);		
        // Transform to DomoCAN UDP Format
		hex_val[0] = '\0';
        sprintf(hex_val, "%8X", frame.can_id);
        strcpy(hex_val, hex_val);
        // Extract SDIH and SIDL
        hex_byte = strndup(hex_val, 4);
		// Convert to 16 bit binary
        htoi(hex_byte, bin_val, bin_str); bin_val[0] = '\0';
		// Transform 16bit CAN to Domocan UDP
        DomoCANaddr(bin_str, bin_val);
        //printf("\n DomoCAN binary SDIH+SIDL:%s\n", bin_val);
        // Convert back form 16bit to 2 HEX bytes
		SID[0] = '\0'; bin_str[0] = '\0';
		bintohex(bin_val,bin_str);
        sprintf(SID,"%s", bin_str);
        SID[4]='\0';
        //printf("\n DomoCAN HEX Destination:%s", SID);
	
	// Determine Length (dlc+4)
        nbytes = frame.can_dlc+4; // Frame length (CAN Header + dlc)
        sprintf(buf, "%0d", nbytes); buf[2]='\0';
        if (nbytes==0) {
          sprintf(buf, "%s",  "00"); buf[2]='\0';
        } else {
          if (nbytes<10)  { sprintf(buf, "0%d", nbytes); buf[2]='\0'; }
          if (nbytes==10) { sprintf(buf, "%s", "0A");    buf[2]='\0'; }
          if (nbytes==11) { sprintf(buf, "%s", "0B");    buf[2]='\0'; }
          if (nbytes==12) { sprintf(buf, "%s", "0C");    buf[2]='\0'; }
          if (nbytes >12) { sprintf(buf, "%s", "0C");    buf[2]='\0'; }
        } // END IF nbytes
		// Extract EIDH and EIDL
        EID[0] = '\0';
		strncpy(EID, hex_val+4, 4); EID[4]='\0';
		// Build UDP Frame
		DomoCANframe[0] = '\0';
        sprintf(DomoCANframe, "%s%s%s%s%s", "70FF", buf, SID, EID, frm_content);
		// Dummy bytes
		if (nbytes<12) {
		  for (i=6+(nbytes*2); i<=29;i++) {
		    DomoCANframe[i]='0';
		  } // END FOR
		} // END IF
		//DomoCANframe[30] = '\0';
		// Calculate Checksum (FCS)
        domocan_checksum(DomoCANframe, fcs);
		DomoCANframe[30] = fcs[0];
		DomoCANframe[31] = fcs[1];
        DomoCANframe[32] = '\0';
		
		// CMD 0x18 on GRAD16 ? ... PUSH and update DB
		if ((!strcmp(SID,"5018")) || (!strcmp(SID,"6018"))) { 
		  //sprintf(cmd, "nohup php5 /var/www/domocan/bin/recv.php %s > nohup.out 2>&1 &", DomoCANframe); // " > /dev/null 2>&1"
		  sprintf(cmd, "php5 /var/www/domocan/bin/recv.php %s &", DomoCANframe); // " > /dev/null 2>&1"
          system(cmd);
		} // ENDIF
		
		// Serialize Message 
        msg_out[0] = '\0';
        raw_len = convert_raw(DomoCANframe, msg_out);
        if (verbose) printf("\n=>TX UDP Frame 0x70: %s, len=%d",DomoCANframe,raw_len);
		// Resets Interupts
	    FD_ZERO(&readfds); // zero out the read set
		FD_SET(sc, &readfds); // add CAN socket to the read set
		FD_SET(sa, &readfds); // add UDP Serveur socket to the read set
		// Prepare packet.
	    // First part is an IPv4 header.
	    memcpy (packet, &iphdr, IP4_HDRLEN);
	    // UDP checksum (16 bits)
        udphdr.check = udp4_checksum (iphdr, udphdr, msg_out, datalen);
	    // Next part of packet is upper layer protocol header.
	    memcpy ((packet + IP4_HDRLEN), &udphdr, UDP_HDRLEN);
	    // Finally, add the UDP data.
	    memcpy (packet + IP4_HDRLEN + UDP_HDRLEN, msg_out, datalen);
	    // Send packet.
	    if (sendto (sd, packet, IP4_HDRLEN + UDP_HDRLEN + datalen, 0, (struct sockaddr *) &sin, sizeof (struct sockaddr)) < 0)  {
          perror ("sendto() failed ");
          exit (EXIT_FAILURE);
        } // END IF sendto
		
		// Resets Interupts
	    FD_ZERO(&readfds); // zero out the read set
		FD_SET(sc, &readfds); // add CAN socket to the read set
		FD_SET(sa, &readfds); // add UDP Serveur socket to the read set
		


		
		
		
		//printf("php5 /var/www/domocan/bin/recv.php %s &\n", DomoCANframe);
		
		//char* arg_list[] = {
		//	"php5",     /* argv[0], le nom du programme. */
		//	"/var/www/domocan/bin/recv.php",
		//	DomoCANframe,
		//	NULL      /* La liste d'arguments doit se terminer par NULL.  */
		//};
		//execvp ("/usr/bin/php5", arg_list);
		
  	  
     } // END IF nbytes
    } // END IF received a CAN frame

    ///////////////////////////
    // Received a UDP packet //
    ///////////////////////////

    if (FD_ISSET(sa, &readfds)) {

      read(sa, udpframe, 32);
//      if (read(sa, udpframe, MAXDG)!=0) {
      // UDP Packet Received
      //strncpy(tmp_string, bufferHexa, sizeof(bufferHexa));
      bufferHexa[0] = '\0'; RX_UDP_Frame[0] ='\0';
      for (i=0;i<16;i++) {
        sprintf(bufferHexa, "%s%0.2X", bufferHexa, udpframe[i]);
      }

      // Extract Command, PCID, ...
      strncpy(UDP_CMD , bufferHexa   ,  2); UDP_CMD[2]='\0'; strcpy(RX_UDP_Frame, UDP_CMD);
      strncpy(PCID    , bufferHexa+2 ,  2); PCID[2]='\0';    strcat(RX_UDP_Frame, PCID);
      strncpy(Nbytes  , bufferHexa+4 ,  2); Nbytes[2]='\0';  xtoi(Nbytes, &udp_bytes);     strcat(RX_UDP_Frame, Nbytes); udp_bytes = udp_bytes - 4;
      strncpy(SID     , bufferHexa+6 ,  4); SID[4]='\0';     strcat(RX_UDP_Frame, SID);
      strncpy(EID     , bufferHexa+10,  4); EID[4]='\0';     strcat(RX_UDP_Frame, EID);
      strncpy(Data    , bufferHexa+14, 16); Data[16]='\0';   strcat(RX_UDP_Frame, Data); RX_UDP_Frame[30]='\0';
      strncpy(FCS     , bufferHexa+30,  2); FCS[2]='\0';

      if ((verbose) && (strcmp(UDP_CMD, "70")) && (strcmp(UDP_CMD, "50"))) { printf("\n\n>RX UDP: %s", bufferHexa); }

      // FCS OK?
      domocan_checksum(RX_UDP_Frame, calc_FCS); calc_FCS[2]='\0';
      //printf("\nRX RAW UDP: %s(UDP_CMD)-%s(PCID)-%d(DLC)-%s/%s(Header)-%s(Data)+%s(FCS)+CalcFCS=%s\nFull Frame= %s\n", UDP_CMD, PCID , udp_bytes, SID, EID, Data, FCS, calc_FCS, RX_UDP_Frame);

      if (!strcmp(FCS, calc_FCS)) {
        // Frame OK (FCS)
        // printf("...fcs OK...UDP_CMD=%s...", UDP_CMD);
        if (!strcmp(UDP_CMD, "60")) {
          // RX UDP_CMD=60 [Send CAN on Bus]
          if (verbose) printf("\nUDP_CMD=60 [Send CAN on BUS, ");

          // Convert SIDH and SIDL to binary (16 bits)
		  DomoCANframe[0]='\0'; bin_str[0]='\0';
		  htoi(SID, bin_val, bin_str);
		  // Convert SIDH and SIDL from UDP to CAN format
		  bin_val[0]='\0';
          UDPaddr_to_CAN(bin_str, bin_val);
		  // Convert back SIDH and SIDL to HEX
		  bintohex(bin_val, FCS); FCS[4]='\0';
		  DomoCANframe[0]='\0';
		  sprintf(DomoCANframe, "%s%s%s", "0x", FCS, EID);
		  DomoCANframe[10]='\0';
		  if (verbose) printf("Destination=%s]", SID);

		  frame.can_id     = strtoul(DomoCANframe, NULL, 0); //*msg_out;
		  frame.can_dlc    = udp_bytes;
		  Data[udp_bytes*2]  = '\0';
		  msg_out[0] = '\0';
          raw_len          = convert_raw(Data, msg_out); // Convert to RAW msg to send;
		  // Prepare CAN frame Data content
          for (i=0; i<=udp_bytes;i++) {
		    frame.data[i] = msg_out[i];
		  }
		  FCS[0]='\0';
          if ((nbytes = write(sc, &frame, sizeof(frame))) != sizeof(frame)) {
		    // Send NACK back to PC
			sprintf(FCS, "%s", "01"); FCS[2]='\0';
			//perror("CAN write __");
		  } else {
		    // Send ACK back to PC
		    sprintf(FCS, "%s", "00"); FCS[2]='\0';
		  } // END IF

          // CAN_err = sendto(s, &frame, sizeof(struct can_frame), 0, (struct sockaddr*)&caddr, sizeof(caddr));
          if (verbose) printf("\n=>TX CAN: Header=%s, Data=%s, DLC=%d, frame len= %d", DomoCANframe, Data, udp_bytes, sizeof(frame));

          // Send UDP Ack back to PC with error code 0=OK, 1=NOK
		  //      Frame = 50 PCID Length=1 ACK/NACK DummyBytes FCS 
          temp_msg[0]='\0';
		  sprintf(temp_msg, "%s%s%s", "50", PCID, "01",  FCS);		  
		  // Dummy bytes
		  for (i=6; i<=29;i++) {
		    temp_msg[i]='0';
		  } // END FOR
		  temp_msg[30] = '\0';
          // Calculating checksum (FCS)
		  domocan_checksum(temp_msg, fcs);
          strcat(temp_msg, fcs); temp_msg[32]='\0';
          raw_len = convert_raw(temp_msg, msg_out);
          printf("\n=>TX UDP CAN Send OK=Frame 0x50): %s",temp_msg);
          //s=sendto(sb, msg_out, 16, 0, (struct sockaddr *)&baddr, sizeof(baddr));

          //FD_ZERO(&readfds); // zero out the read set

		  // Prepare packet.
		  // First part is an IPv4 header.
		  memcpy (packet, &iphdr, IP4_HDRLEN);
		  // UDP checksum (16 bits)
          udphdr.check = udp4_checksum (iphdr, udphdr, msg_out, datalen);
		  // Next part of packet is upper layer protocol header.
		  memcpy ((packet + IP4_HDRLEN), &udphdr, UDP_HDRLEN);
		  // Finally, add the UDP data.
		  memcpy (packet + IP4_HDRLEN + UDP_HDRLEN, msg_out, datalen);
		  // Send packet.
		  if (sendto (sd, packet, IP4_HDRLEN + UDP_HDRLEN + datalen, 0, (struct sockaddr *) &sin, sizeof (struct sockaddr)) < 0)  {
			perror ("sendto() failed ");
			exit (EXIT_FAILURE);
		  } //END IF sendto


        } else { // END IF UDP_CMD=60

          if (!strcmp(UDP_CMD, "41")) {
            // RX UDP_CMD=41 [Stop Transmission]
            printf("\nRX UDP_CMD=41 [Stop Transmission]");


          } else { // END IF UDP_CMD=41
            if (!strcmp(UDP_CMD, "42")) {
              // RX UDP_CMD=42 [Change CAN Filter]
              printf("\nRX UDP_CMD=42 [Change CAN Filter]");

            } else { // END IF UDP_CMD=42
              if (!strcmp(UDP_CMD, "43")) {
                // RX UDP_CMD=43 [View CAN Filter]
                printf(" - RX UDP_CMD=43 [View CAN Filter ... ACK?]");

                // Send back Empty Filter
                msg_out[0]='\0'; temp_msg[0]='\0';
				sprintf(temp_msg, "%s%s%s", "52", PCID, "09");
				// Dummy bytes
				for (i=6; i<=30;i++) {
				  temp_msg[i]='0';
				} // END FOR
				temp_msg[30] = '\0';
                domocan_checksum(temp_msg, fcs);
                strcat(temp_msg, fcs); temp_msg[32]='\0';

                raw_len = convert_raw(temp_msg, msg_out);

                printf("\n=>TX UDP ACK! %s",temp_msg);
                //s=sendto(sb, msg_out, 16, 0, (struct sockaddr *)&baddr, sizeof(baddr));

			    // Prepare packet.
			    // First part is an IPv4 header.
			    memcpy (packet, &iphdr, IP4_HDRLEN);
				// UDP checksum (16 bits)
                udphdr.check = udp4_checksum (iphdr, udphdr, msg_out, datalen);
			    // Next part of packet is upper layer protocol header.
			    memcpy ((packet + IP4_HDRLEN), &udphdr, UDP_HDRLEN);
			    // Finally, add the UDP data.
			    memcpy (packet + IP4_HDRLEN + UDP_HDRLEN, msg_out, datalen);
			    // Send packet.
			    if (sendto (sd, packet, IP4_HDRLEN + UDP_HDRLEN + datalen, 0, (struct sockaddr *) &sin, sizeof (struct sockaddr)) < 0)  {
				  perror ("sendto() failed ");
				  exit (EXIT_FAILURE);
			    } // END IF sendto

                //sendto(sock,msg_out,raw_len,0,(struct sockaddr*)&UDPclient,clsize);

              } else { // END IF UDP_CMD=43
                if (!strcmp(UDP_CMD, "44")) {
                  // RX UDP_CMD=44 [Change CAN Param]
                  printf("\nRX UDP_CMD=44 [Change CAN Param]");

                } else { // END IF UDP_CMD=44
                  if (!strcmp(UDP_CMD, "45")) {
                    // RX UDP_CMD=45 [View CAN Param]
                    printf("\nRX UDP_CMD=45 [View CAN Param]");

                  } // END IF RX UDP_CMD=45
                } // END IF RX UDP_CMD=44
              } // END IF RX UDP_CMD=43
            } // END IF RX UDP_CMD=42
          } // END IF RX UDP_CMD=41
        } // END IF RX UDP_CMD=60

      } else {
        if ((verbose) && (!strcmp(UDP_CMD, "70")))  printf("\nRX UDP FCS NOK! (%s-%s<>%s)\n", RX_UDP_Frame, FCS, calc_FCS);
      } // END IF FCS ok




//      } // END IF read(sa)
    } // END IF Received a UDP packet
  } // END WHILE

close(sc);
close(sa);
close(sd);

// Free allocated memory.
  free (data);
  free (packet);
  free (interface);
  free (target);
  free (src_ip);
  free (dst_ip);
  free (ip_flags);

return 0;
} // END main

