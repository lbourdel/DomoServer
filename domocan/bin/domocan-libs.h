// Domocan bridge libs

// Determine Interface IP Adrress and Broadcast Address
#define INT_TO_ADDR(_addr) \
(_addr & 0xFF), \
(_addr >> 8 & 0xFF), \
(_addr >> 16 & 0xFF), \
(_addr >> 24 & 0xFF)

void get_IP_conf(char intf[5], char intf_addr[16], char intf_bcast[16]) {
  // Funtion WHich determines teh IP Address of the Ethernet Inetrface used for the client oscket
  struct ifconf ifc;
  struct ifreq ifr[10];
  int sd, ifc_num, addr, bcast, mask, network, i;
  // Create a socket so we can use ioctl on the file 
  // descriptor to retrieve the interface info. 
  sd = socket(PF_INET, SOCK_DGRAM, 0);
  if (sd > 0) {
    ifc.ifc_len = sizeof(ifr);
    ifc.ifc_ifcu.ifcu_buf = (caddr_t)ifr;
    if (ioctl(sd, SIOCGIFCONF, &ifc) == 0) {
      ifc_num = ifc.ifc_len / sizeof(struct ifreq);
      //printf("%d interfaces found\n", ifc_num);
      for (i = 0; i < ifc_num; ++i) {
        if (ifr[i].ifr_addr.sa_family != AF_INET) { continue; }
        // display the interface name
        //printf("%d) interface: %s\n", i+1, ifr[i].ifr_name);
        // Retrieve the IP address, broadcast address, and subnet mask.
        if (ioctl(sd, SIOCGIFADDR, &ifr[i]) == 0) {
          addr = ((struct sockaddr_in *)(&ifr[i].ifr_addr))->sin_addr.s_addr;
        } // END IF
        if (ioctl(sd, SIOCGIFBRDADDR, &ifr[i]) == 0) {
          bcast = ((struct sockaddr_in *)(&ifr[i].ifr_broadaddr))->sin_addr.s_addr;
        } // END IF
        if (!strcmp(ifr[i].ifr_name, intf)) {
          // Found Interface => Copy Address and Broadcast
          sprintf(intf_addr, "%d.%d.%d.%d\0", INT_TO_ADDR(addr));
          sprintf(intf_bcast, "%d.%d.%d.%d\0", INT_TO_ADDR(bcast));
		} // END IF strcmp
      } // END FOR                      
    } // END IF ioctl
    close(sd);
  } // END IF (sd > 0)
} // END void get_IP_conf

// Checksum function
unsigned short int
checksum (unsigned short int *addr, int len)
{
  int nleft = len;
  int sum = 0;
  unsigned short int *w = addr;
  unsigned short int answer = 0;

  while (nleft > 1) {
    sum += *w++;
    nleft -= sizeof (unsigned short int);
  }

  if (nleft == 1) {
    *(unsigned char *) (&answer) = *(unsigned char *) w;
    sum += answer;
  }

  sum = (sum >> 16) + (sum & 0xFFFF);
  sum += (sum >> 16);
  answer = ~sum;
  return (answer);
}

// Build IPv4 UDP pseudo-header and call checksum function.
unsigned short int
udp4_checksum (struct ip iphdr, struct udphdr udphdr, unsigned char *payload, int payloadlen) {
  char buf[IP_MAXPACKET];
  char *ptr;
  int chksumlen = 0;
  int i;
  // ptr points to beginning of buffer buf
  ptr = &buf[0];  
  // Copy source IP address into buf (32 bits)
  memcpy (ptr, &iphdr.ip_src.s_addr, sizeof (iphdr.ip_src.s_addr));
  ptr += sizeof (iphdr.ip_src.s_addr);
  chksumlen += sizeof (iphdr.ip_src.s_addr);
  // Copy destination IP address into buf (32 bits)
  memcpy (ptr, &iphdr.ip_dst.s_addr, sizeof (iphdr.ip_dst.s_addr));
  ptr += sizeof (iphdr.ip_dst.s_addr);
  chksumlen += sizeof (iphdr.ip_dst.s_addr);
  // Copy zero field to buf (8 bits)
  *ptr = 0; ptr++;
  chksumlen += 1;
  // Copy transport layer protocol to buf (8 bits)
  memcpy (ptr, &iphdr.ip_p, sizeof (iphdr.ip_p));
  ptr += sizeof (iphdr.ip_p);
  chksumlen += sizeof (iphdr.ip_p);
  // Copy UDP length to buf (16 bits)
  memcpy (ptr, &udphdr.len, sizeof (udphdr.len));
  ptr += sizeof (udphdr.len);
  chksumlen += sizeof (udphdr.len);
  // Copy UDP source port to buf (16 bits)
  memcpy (ptr, &udphdr.source, sizeof (udphdr.source));
  ptr += sizeof (udphdr.source);
  chksumlen += sizeof (udphdr.source);
  // Copy UDP destination port to buf (16 bits)
  memcpy (ptr, &udphdr.dest, sizeof (udphdr.dest));
  ptr += sizeof (udphdr.dest);
  chksumlen += sizeof (udphdr.dest);
  // Copy UDP length again to buf (16 bits)
  memcpy (ptr, &udphdr.len, sizeof (udphdr.len));
  ptr += sizeof (udphdr.len);
  chksumlen += sizeof (udphdr.len);
  // Copy UDP checksum to buf (16 bits)
  // Zero, since we don't know it yet
  *ptr = 0; ptr++;
  *ptr = 0; ptr++;
  chksumlen += 2;
  // Copy payload to buf
  memcpy (ptr, payload, payloadlen);
  ptr += payloadlen;
  chksumlen += payloadlen;
  // Pad to the next 16-bit boundary
  for (i=0; i<payloadlen%2; i++, ptr++) {
    *ptr = 0;
    ptr++;
    chksumlen++;
  } // END FOR
  return checksum ((unsigned short int *) buf, chksumlen);
} // END FUNCTION UDP_checksum


char *StringPadRight(char *string, int padded_len, char *pad) {
  // String Padding to right
    int len = (int) strlen(string);
    if (len >= padded_len) {
        return string;
    } // END IF len
    int i;
    for (i = 0; i < padded_len - len; i++) {
        strcat(string, pad);
    } // END FOR
    return string;
} // END FUNCTION *StringPadRight

void htoi(const char *ptr, char *binAddr, char value[32]) {
  // Hexa to bin Function
  sprintf(value,"%s","");
  char ch = *ptr;
  int i;
  const char* quads[] = {"0000", "0001", "0010", "0011", "0100", "0101",
                     "0110", "0111", "1000", "1001", "1010", "1011",
                     "1100", "1101", "1110", "1111"};
  while (ch == ' ' || ch == '\t')
    ch = *(++ptr);

  for (i = 0; i < 5; i++) {
    if (ch >= '0' && ch <= '9')
        strncat(value, quads[ch - '0'], 4);
    if (ch >= 'A' && ch <= 'F')
        strncat(value, quads[10 + ch - 'A'], 4);
    if (ch >= 'a' && ch <= 'f')
        strncat(value, quads[10 + ch - 'a'], 4);
    ch = *(++ptr);
    //printf("\nhtoi= %s\n", value);
  } // END FOR

  *binAddr = *value;
} // END FUNCTION htoi

void bintohex(char binaryNumber[32], char hex[2]) {
  // Binary to Hexa Function
  int i=0, j=0, k=0, l=0, m=0, h[]={0,0};
  char bit[0], dechex[16]={"0123456789ABCDEF"}, val;
  //printf("\nbinary IN=%s\n",binaryNumber);
  for (j=0; j<=3;j++) {
    h[j] = 0; l = 0;
    for (i=0; i<=3; i++) {
       k = j*4+i;
       strncpy(bit, binaryNumber+k, 1);
       m=0; if (bit[0]=='1') m=1;
       l = (l * 2) + m;
       val = dechex[l];
       //printf("\nbit(%i)=", k); printf(" - %s - ",bit); printf("%2i",l); printf(" = %c\n",val);
    } // END FOR i
    hex[j] = val;
  } // END FOR j
} // END FUNCTION bintohex

void DomoCANaddr(char *value, char shifted[18]) {
  // Transform CAN to UDP Domocan Address
  char in[18];
  strcpy(in,value);
  
  // bit Mapping
  // SIDH
  shifted[0]  = in[3];
  shifted[1]  = in[4];
  shifted[2]  = in[5];
  shifted[3]  = in[6];
  shifted[4]  = in[7];
  shifted[5]  = in[8];
  shifted[6]  = in[9];
  shifted[7]  = in[10];
  
  shifted[8]  = '0';
  shifted[9]  = '0';
  shifted[10] = '0';
  // SIDL
  shifted[11] = in[11];
  shifted[12] = in[12];
  shifted[13] = in[13];
  shifted[14] = in[14];
  shifted[15] = in[15];
  // EOS
  shifted[16] = '\0';
  //printf("\n Shifted Destination: [%s]\n", shifted);
} // END FUNCTION DomoCANaddr

void UDPaddr_to_CAN(char *value, char shifted[18]) {
  // Transform UDP to CAN Domocan Address
  char in[18];
  strcpy(in,value);

  // bit Mapping
  shifted[0]  = '1';
  shifted[1]  = '0';
  shifted[2]  = '0';
  // SIDH
  shifted[3]  = in[0];
  shifted[4]  = in[1];
  shifted[5]  = in[2];
  shifted[6]  = in[3];
  shifted[7]  = in[4];
  shifted[8]  = in[5];
  shifted[9]  = in[6];
  shifted[10] = in[7];
  // SIDL
  shifted[11] = in[11];
  shifted[12] = in[12];
  shifted[13] = in[13];
  shifted[14] = in[14];
  shifted[15] = in[15];
  // EOS
  shifted[16] = '\0';
  //printf("\n UDP to CAN Shifted Destination: [%s]\n", shifted);
} // END FUNCTION UDP_to_CAN

void domocan_checksum(char frame[32], char fcs[2]) {
  // Checksum (FCS) for UDP Domocan Frame
  int j=0, sum=0, offset=0;
  char byte[1], temp[2], sumstr[3];
  for (j=0; j<=28;j=j+2) {
       strncpy(byte, frame+j, 2);
       sum = sum + strtol(byte,NULL,16);
  } // END FOR
  // Convert to Decimal value
  sprintf(sumstr, "%d", sum);
  // Convert to HEX
  sprintf(temp, "%X", strtol(sumstr,NULL,10));
  // Output Checksum value, with correct length
  if (strlen(temp)>2) offset =1;
  strncpy(fcs, temp+offset, 2);
} // END FUNCTION domocan_checksum

int xtoi(const char* xs, unsigned int* result) {
  // Converts a hexadecimal string to integer
  size_t szlen = strlen(xs);
  int i, xv, fact;
  if (szlen > 0) {
    // Converting more than 32bit hexadecimal value?
    if (szlen>8) return 2; // exit
    // Begin conversion here
    *result = 0;
    fact = 1;
    // Run until no more character to convert
    for(i=szlen-1; i>=0 ;i--) {
      if (isxdigit(*(xs+i))) {
        if (*(xs+i)>=97) {
          xv = ( *(xs+i) - 97) + 10;
        } else if ( *(xs+i) >= 65) {
          xv = (*(xs+i) - 65) + 10;
        } else {
          xv = *(xs+i) - 48;
        } // END IF
        *result += (xv * fact);
        fact *= 16;
      } else {
        // Conversion was abnormally terminated
        // by non hexadecimal digit, hence
        // returning only the converted with
        // an error value 4 (illegal hex character)
        return 4;
      } // END IF
    } // END FOR
 } // END IF
 // Nothing to convert
 return 1;
} // END FUNCTION xtoi

int convert_raw(char frame[32], char raw[64]) {
  // Serialize Message, Convert to RAW stream to feed sento command
  int i, ascii;
  char byte[1], hex_byte[1];
  sprintf(raw,"%s","");
  sprintf(frame, "%s", frame);
  //printf("\nRAW convert:\n");
  for (i=0; i<strlen(frame);i=i+2) {
    strncpy(byte,frame+i, 2); byte[2] = '\0';
    sprintf(hex_byte, "%X", byte[0,1]);
    xtoi(strncpy(byte,frame+i, 2), &ascii);
    //printf("%s,0x%X = (%d) [%c]", byte, hex_byte[0,1], ascii, ascii);
    sprintf(hex_byte, "%c", ascii);
    raw[i/2] = hex_byte[0];
    //printf("\n");
  } // END FOR
  return i/2;
} // END FUNCTION convert_raw

