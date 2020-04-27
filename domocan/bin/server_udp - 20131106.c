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
int i;
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
  while (1) {

    recvfrom(sock, buffer, sizeof(buffer), 0, (SOCKADDR *)&cl, &clsize);
    strncpy(tmp, bufferHexa, sizeof(bufferHexa));

    bufferHexa[0] = 0;

    for (i=0;i<clsize;i++) {
      sprintf(bufferHexa, "%s%0.2X", bufferHexa, buffer[i]);
    }

//    if ( strcmp(tmp, bufferHexa) ) {
      printf("domocan-server UDP frame IN: tmp= %s / bufferHexa= %s (len=%i)\n", tmp, bufferHexa, len);
	  //sprintf(cmd, "php /var/www/domocan/bin/recv.php %s", bufferHexa);
	  sprintf(cmd, "php /var/www/domocan/bin/recv.php %s", tmp);
      system(cmd);
//    }

  }

  closesocket(sock);
  }

}

