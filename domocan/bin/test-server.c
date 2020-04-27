#include <sys/socket.h>
#include <netinet/in.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include <fcntl.h>
#include <time.h>

/* DECLARATION DES VARIABLES */
#define BUFSIZE 16
unsigned char buffer[BUFSIZE];

unsigned char bufferHexa[BUFSIZE * 2 + 1];
unsigned char tmp[BUFSIZE * 2 + 1];
unsigned char cycle_buff[BUFSIZE * 2 + 1];
unsigned char cycle_buff_2[BUFSIZE * 2 + 1];
unsigned char tst[BUFSIZE];
int i, len;
char * pos;
char cmd[255];

int main() {

  // Init
  strcpy(cycle_buff,""); 
  
  /// First frame
  memset(bufferHexa, '\0', sizeof(bufferHexa));
  sprintf(bufferHexa,"70FF0A010100000D3B0657060C000032");
  //sprintf(bufferHexa,"000000000000000070FF0A010100000D");
  //sprintf(bufferHexa,"3B0657060C0000320000000000000000"); sprintf(cycle_buff,"70FF0A010100000D");
 
	  len = 0;
	  if (strstr(bufferHexa,"70FF")!=NULL) { pos = strstr(bufferHexa,"70FF"); len = strlen(pos); //printf("OK (%i %s)\n",len, pos); }
	  //printf("Where is 7? %i\n", len);
	  if (len!=32) {
	    if (strlen(cycle_buff)==0) {
		  // cycle_buff Empty, first frame received, out of UDP framing
		  memset(cycle_buff, '\0', sizeof(cycle_buff));
		  strcpy(cycle_buff,pos);
		  printf("First pass= %s\n",cycle_buff);
		  memset(bufferHexa, '\0', sizeof(bufferHexa));
		} else {
		  // cycle_buff filled with first frame, second frame out of UDP framing received?
		  len = 0;
	      if (strstr(bufferHexa,"70FF")!=NULL) { pos = strstr(bufferHexa,"70FF"); len = strlen(pos); printf("OK (%i %s)\n",len, pos); }
		  // if len!=32 ... Second frame
		  if (len!=32) {
		    strncpy(cycle_buff_2, bufferHexa, (32-strlen(cycle_buff)));
		    memset(bufferHexa, '\0', sizeof(bufferHexa));
		    snprintf(bufferHexa, sizeof bufferHexa, "%s%s", cycle_buff, cycle_buff_2);
		    //printf("FULL Frame = %s\n\n", bufferHexa);
		    sprintf(cmd, "php /var/www/domocan/bin/recv.php %s", bufferHexa);
            system(cmd);
		  } else {
		    // FULL frame received...
			sprintf(cmd, "php /var/www/domocan/bin/recv.php %s", bufferHexa);
            system(cmd);
		    memset(bufferHexa, '\0', sizeof(bufferHexa));
		  }

		}
	  } else {
	    // FULL Frame Received on empty buffer
        //printf(" ***70 Frame*** php /var/www/domocan/bin/recv.php %s\n", bufferHexa);
		sprintf(cmd, "php /var/www/domocan/bin/recv.php %s", bufferHexa);
        system(cmd);
		memset(bufferHexa, '\0', sizeof(bufferHexa));
	  }

}