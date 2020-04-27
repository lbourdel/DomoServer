#include <sys/socket.h> 
#include <netinet/in.h> 
#include <stdio.h> 
#include <unistd.h> 
#include <string.h> 
#include <fcntl.h> 
#include <time.h> 

#define closesocket(s) close(s) 
#define BUFSIZE 16 

typedef int SOCKET; 
typedef struct sockaddr_in SOCKADDR_IN; 
typedef struct sockaddr SOCKADDR; 

/* STRUCTURE DU SERVEUR */ 
SOCKADDR_IN srv = { 0 }; 

/* STRUCTURE DU CLIENT */ 
SOCKADDR_IN cl = { 0 }; 

/* DECLARATION DES VARIABLES */ 
unsigned char buffer[BUFSIZE * 2]; 
unsigned char buffer2[BUFSIZE]; 
unsigned char bufferTemp[BUFSIZE]; 
int clsize = sizeof(cl); 
unsigned char bufferHexa[BUFSIZE * 2 + 1]; 
char cmd[255]; 
int forkPid; 
int port = 1470; 

/////////////////////////////////////////////////////////// 
// Recompose la trame CAN si 0x70 0xFF est trouvé 
// renvoi 1 si OK, 0 sinon 
/////////////////////////////////////////////////////////// 
unsigned char ucFrameCheck(unsigned char *ucVal) 
{ 
int i, j, pos; 
unsigned char bHeaderOK = 0; 
unsigned char *ucPtr = NULL; 

unsigned char buff1[BUFSIZE]; 
unsigned char buff2[BUFSIZE]; 

ucPtr = ucVal; 

// Réccupère la trame à traiter 
for (i = 0; i < BUFSIZE; i++) 
{ 
buff2[i] = *(ucPtr++); 
} 

// Recherche de l'entête 70FF 
i = 0; 
do 
{ 
if ((buff2[i] == 0x70) && (buff2[i + 1] == 0xFF)) 
{ 
bHeaderOK = 1; 
} 

i++; 
} 
while ((i < BUFSIZE) && (bHeaderOK == 0)); 

if (bHeaderOK == 1) 
{ 
// Entête trouvé 
for (j = 0; j < BUFSIZE; j++) 
{ 
buff1[j] = 0; 
} 

// Réorganisation du debut de trame 
pos = (i - 1); 
j = 0; 

for (i = 0; i < (BUFSIZE - pos); i++) 
{ 
buff1[j] = buff2[i + pos]; 
j++; 
} 

// Réorganisation de fin de trame 
for (i = 0; i < pos; i++) 
{ 
buff1[(BUFSIZE - pos) + i] = buff2[i]; 
} 

// Renvoi la trame reconstitué 
ucPtr = ucVal; 
for (i = 0; i < BUFSIZE; i++) 
{ 
*(ucPtr++) = buff1[i]; 
} 
} 

return (bHeaderOK); 
} 

/////////////////////////////////////////////////////////// 
// Fonction comparaison de 2 buffer 
// si buffer1 != buffer2 return 1 sinon 0 
/////////////////////////////////////////////////////////// 
unsigned char bCompareFrame(unsigned char *buf1, unsigned char *buf2) 
{ 
int i; 
unsigned char ucResult = 0; 
unsigned char bufTmp1[BUFSIZE]; 
unsigned char bufTmp2[BUFSIZE]; 
unsigned char *ptr1 = NULL; 
unsigned char *ptr2 = NULL; 

// Remplissage buffers 
ptr1 = buf1; 
ptr2 = buf2; 
for (i = 0; i < BUFSIZE; i++) 
{ 
bufTmp1[i] = *(ptr1++); 
bufTmp2[i] = *(ptr2++); 
} 

// Comparaison 
ucResult = 0; 
i = 0; 
do 
{ 
if (bufTmp1[i] != bufTmp2[i]) 
{ 
// différence trouvé 
ucResult = 1; 
} 

i++; 
} 
while ((i < BUFSIZE) && (ucResult == 0)); 

return(ucResult); 
} 

/////////////////////////////////////////////////////////// 
/////////////////////////////////////////////////////////// 
int main() 
{ 
unsigned char	sum, trameOK; 
int i, j, len, k; 
int comResult = 0; 

// INITIALISATION DU SOCKET 
SOCKET sock = socket(AF_INET, SOCK_DGRAM, 0); 

if ((forkPid = fork()) < 0) 
perror("fork()"); 

if (sock == -1) 
perror("socket()"); 

if (!forkPid) 
{ 
// DEFINITION DE LA STRUCTURE 
srv.sin_addr.s_addr = htonl(INADDR_ANY); 
srv.sin_family = AF_INET; 
srv.sin_port = htons(port); 

// ECOUTE 
if (bind(sock, (SOCKADDR *) &srv, sizeof(srv)) == -1) 
{ 
puts("erreur"); 
perror("bind()"); 
} 

// ATTENTE DE RECEPTION ET TRAITEMENT 
for (i = 0; i < BUFSIZE; i++) 
{ 
buffer[i] = 0; 
buffer2[i] = 0; 
} 

//k = 0; 

while (1) 
{ 
//k++; 

comResult = recvfrom(sock, buffer, sizeof(buffer), 0, (SOCKADDR *)&cl, &clsize); 

if ((clsize == BUFSIZE) && (comResult != 0)) 
{ 
// Prendre en compte uniquement les trames de 16 octets 

// pour debug	
printf("Trame hexa brute : "); 
for (i = 0; i < BUFSIZE; i++) 
{ 
printf("%02X", buffer[i]); 
} 
printf("\n"); 
// fin pour debug */ 

trameOK = 0; 

if ((buffer[0] == 0x70) && (buffer[1] == 0xFF)) 
{ 
// Trame reçu avec un header OK 

// pour debug	
printf("Trame header OK : "); 
for (i = 0; i < BUFSIZE; i++) 
{ 
printf("%02X", buffer[i]); 
} 
printf("\n"); 
// fin pour debug */ 

// Calcul du checksum pour vérification 
sum = 0; 
for (i = 0; i < 15; i++) 
{ 
sum += buffer[i]; 
} 
sum = (sum % 256); 

if (sum == buffer[15]) 
{ 
// Checksum OK pour la trame complète 
trameOK = 1; 
} 
} 
else 
{ 
// Vérifie si la trame est mélangé et la remets dans l'ordre 
if (ucFrameCheck(buffer) == 1) 
{ 
// Le header est present 

// pour debug	
printf("Trame re-ordonnée: "); 
for (i = 0; i < BUFSIZE; i++) 
{ 
printf("%02X", buffer[i]); 
} 
printf("\n"); 
// fin pour debug */ 

// Calcul du checksum pour vérification 
sum = 0; 
for (i = 0; i < 15; i++) 
{ 
sum += buffer[i]; 
} 
sum = (sum % 256); 

if (sum == buffer[15]) 
{ 
// Checksum OK pour la trame complète 
trameOK = 1; 
} 
} 
else 
{ 
// pas de header 
if (buffer[0] == 0xFF) 
{ 
// Reconstruction: manque l'entête 0x70 
for (j = 0; j < 15; j++) 
{ 
bufferTemp[j] = buffer[j]; 
}	

buffer[0] = 0x70; 

for (j = 0; j < 15; j++) 
{ 
buffer[j + 1] = bufferTemp[j]; 
}	

// Calcul du checksum 
buffer[15] = 0; 
sum = 0; 
for (i = 0; i < 15; i++) 
{ 
sum += buffer[i]; 
} 
buffer[15] = (sum % 256); 

// pour debug	
printf("Trame avec 70 : "); 
for (i = 0; i < BUFSIZE; i++) 
{ 
printf("%02X", buffer[i]); 
} 
printf("\n"); 
// fin pour debug*/ 

trameOK = 1; 
} 
else if ((buffer[0] == 0x18) && (buffer[1] == 0x01)) 
{ 
// Reconstruction: manque 0x70 0xFF 0x05 0x50 
for (j = 0; j < 12; j++) 
{ 
bufferTemp[j] = buffer[j]; 
}	

buffer[0] = 0x70; 
buffer[1] = 0xFF; 
buffer[2] = 0x05; 
buffer[3] = 0x50; 

for (j = 0; j < 12; j++) 
{ 
buffer[j + 4] = bufferTemp[j]; 
}	

// Calcul du checksum 
buffer[15] = 0; 
sum = 0; 
for (i = 0; i < 15; i++) 
{ 
sum += buffer[i]; 
} 
buffer[15] = (sum % 256); 

// pour debug	
printf("Trame avec 70FF0550: "); 
for (i = 0; i < BUFSIZE; i++) 
{ 
printf("%02X", buffer[i]); 
} 
printf("\n"); 
// fin pour debug */ 

trameOK = 1; 
} 
else 
{ 
// Reconstruction: manque l'entête 0x70 0xFF 
for (j = 0; j < 14; j++) 
{ 
bufferTemp[j] = buffer[j]; 
}	

buffer[0] = 0x70; 
buffer[1] = 0xFF; 

for (j = 0; j < 14; j++) 
{ 
buffer[j + 2] = bufferTemp[j]; 
}	

// Calcul du checksum 
buffer[15] = 0; 
sum = 0; 
for (i = 0; i < 15; i++) 
{ 
sum += buffer[i]; 
} 
buffer[15] = (sum % 256); 

// pour debug	
printf("Trame avec 70FF : "); 
for (i = 0; i < BUFSIZE; i++) 
{ 
printf("%02X", buffer[i]); 
} 
printf("\n"); 
// fin pour debug */ 

trameOK = 1; 
} 
} 
} 

if (trameOK == 1) 
{ 
// Entête OK, checksum OK 

// Vérifie si on a pas déjà reçu la trame 
if (bCompareFrame(buffer, buffer2) == 1) 
{ 
// OK nouvelle trame 

// pour debug	
printf("Trame ok : "); 
for (i = 0; i < BUFSIZE; i++) 
{ 
printf("%02X", buffer[i]); 
} 
printf("\n"); 
// fin pour debug */ 

// Sauvegarde pour le prochain cycle 
for (i = 0; i < BUFSIZE; i++) 
{ 
buffer2[i] = buffer[i]; 
} 

// vérification du destinataire et la taille de trame doit pas dépasser 12 octets 
//if ( ((buffer[3] == 0x01) || (buffer[3] == 0x50) || (buffer[3] == 0x60)) && (buffer[2] <= 12) ) 
if ( ((buffer[3] == 0x50) || (buffer[3] == 0x60)) && (buffer[2] <= 12) ) 
{ 
// destinataire accepté et taille accepté 

// Conversion en ASCII 
bufferHexa[0] = 0; 
for (i = 0; i < BUFSIZE; i++) 
{ 
sprintf(bufferHexa, "%s%0.2X", bufferHexa, buffer[i]); 
} 

// pour debug 
printf("Trame ASCII : %s\n", bufferHexa); 
// fin pour debug 

// Envoi pour traitement PHP 
sprintf(cmd, "php /var/www/domocan/bin/recv.php %s", bufferHexa); 
system(cmd); 
// Efface le buffer pour prochain cycle 
memset(bufferHexa, '\0', sizeof(bufferHexa)); 
} 
} 
} 
} 
} 

closesocket(sock); 
} 
} 