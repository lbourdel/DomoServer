TO DO
copy systemd files to /lib/systemd/system/

enable each services:
sudo systemctl enable can0.service domocan-bridge.service gcalendar.timer gcalendar.service

sudo systemctl start can0.service domocan-bridge.service gcalendar.timer gcalendar.service

sudo systemctl status can0.service domocan-bridge.service gcalendar.timer gcalendar.service


