DomoCAN-EM
==========

DomoCAN Eventually Mastered (Web based Independant Central Controller)

0. Credit: This work and major part has been delivered by Benoitd54 (Initial Post on http://domocan.heberg-forum.net/ftopic54-0.html )

1. Prerequisite are the following:

- Linux installed (no Apache installed)
- NGINX server, not the satndard package, but compiled with the PUSH module ( http://pushmodule.slact.net/ )
- MySQL (preferably with phpmyadmin)
- PHP CLI (inclued in nginx with FCGI)
- You will have to represent your house using SWEET HOME 3D (long process).
- If you want to use the audio modules (music), you have to install MPD daemon and its MPC client.

2. Install Instructions:

- Copy all directories/files (www, class, etc) into /var/domocan/www/
- Recompile /var/www/domocan/bin/server_udp

3. Finetuning:
Edit /var/domocan/www/conf/config.php in regards to your machine/system settings

4. Enjoy:
User Access://ip-server/domocan/www/index.php Admin : http://ip-server/domocan/admin phpmyadmin : http://ip-server/phpmyadmin

5. Annexes:
5.1. NGINX Install tuto: Download NGINX

- Download NGINX:
wget http://nginx.org/download/nginx-1.0.2.tar.gz

- Download push Module:
wget http://pushmodule.slact.net/downloads/nginx_http_push_module-0.692.tar.gz
- UNcompress both using tar -zvxf,
- Install nginx, in nginx directory:

./configure --prefix=/usr/local/nginx/ --add-module=/où/sont/les/sources/du/module/push/
make
make install
