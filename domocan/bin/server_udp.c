#include <sys/socket.h>
#include <netinet/in.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include <fcntl.h>
#include <time.h>

#define closesocket(s) close(s)

typedef int SOCKET;
typedef struct sockaddr_in SOCKADDR_IN;
typedef struct sockaddr SOCKADDR;

/* STRUCTURE DU SERVEUR */
SOCKADDR_IN srv = { 0 };

/* STRUCTURE DU CLIENT */
SOCKADDR_IN cl = { 0 };

/* DECLARATION DES VARIABLES */
#define BUFSIZE 16
unsigned char buffer[BUFSIZE];
int clsize = sizeof(cl);
unsigned char bufferHexa[BUFSIZE * 2 + 1];
unsigned char tmp[BUFSIZE * 2 + 1];
unsigned char cycle_buff[BUFSIZE * 2 + 1];
unsigned char cycle_buff_2[BUFSIZE * 2 + 1];
unsigned char tst[BUFSIZE];
int i, len;
char * pos;
char cmd[255];
int forkPid;
int port = 1470;


int main() {

  /* INITIALISATION DU SOCKET */
  SOCKET sock = socket(AF_INET, SOCK_DGRAM, 0);

  if ((forkPid = fork()) < 0)
	perror("fork()");

  if (sock == -1) {
    perror("socket()");
  }

  if (!forkPid)
  {

  /* DEFINITION DE LA STRUCTURE */
  srv.sin_addr.s_addr = htonl(INADDR_ANY);
  srv.sin_family = AF_INET;
  srv.sin_port = htons(port);

  /* ECOUTE */
  if ( bind(sock, (SOCKADDR *) &srv, sizeof(srv)) == -1) {
    puts("erreur");
    perror("bind()");
  }

  /* ATTENTE DE RECEPTION ET TRAITEMENT */
  strcpy(cycle_buff,""); 
  while (1) {

    recvfrom(sock, buffer, sizeof(buffer), 0, (SOCKADDR *)&cl, &clsize);
    strncpy(tmp, bufferHexa, sizeof(bufferHexa));

    bufferHexa[0] = 0;

    for (i=0;i<clsize;i++) {
      sprintf(bufferHexa, "%s%0.2X", bufferHexa, buffer[i]);
    }


	  len = 0;
	  if (strstr(bufferHexa,"70FF")!=NULL) { 
	    pos = strstr(bufferHexa,"70FF"); 
		len = strlen(pos); 
		//printf("OK (%i %s)\n",len, pos); 
	  }
	  printf("Where is 7 (UDP Frame: %s)? %i\n", bufferHexa, len);
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
	      if (strstr(bufferHexa,"70FF")!=NULL) { 
		    pos = strstr(bufferHexa,"70FF"); 
			len = strlen(pos); 
			//printf("OK (%i %s)\n",len, pos); 
		  }
		  // if len!=32 ... Second frame
		  if (len!=32) {
		    strncpy(cycle_buff_2, bufferHexa, (32-strlen(cycle_buff)));
		    memset(bufferHexa, '\0', sizeof(bufferHexa));
		    snprintf(bufferHexa, sizeof(bufferHexa), "%s%s", cycle_buff, cycle_buff_2);
		    printf("Second Pass, FULL Frame = %s\n\n", bufferHexa);
		    sprintf(cmd, "php /var/www/domocan/bin/recv.php %s &", bufferHexa);
            system(cmd);
		  } else {
		    // FULL frame received...
			printf("Second Pass, ***70*** Frame! = %s\n\n", bufferHexa);
			sprintf(cmd, "php /var/www/domocan/bin/recv.php %s &", bufferHexa);
            system(cmd);
		    memset(bufferHexa, '\0', sizeof(bufferHexa));
		  }

		}
	  } else {
	    // FULL Frame Received on empty buffer
        printf("***70 Frame*** php /var/www/domocan/bin/recv.php %s\n", bufferHexa);
		sprintf(cmd, "php /var/www/domocan/bin/recv.php %s &", bufferHexa);
        system(cmd);
		memset(bufferHexa, '\0', sizeof(bufferHexa));
	  }


  }

  closesocket(sock);
  }

}

