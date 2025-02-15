#!/bin/bash
clear
echo MeshDash Install-Script V 1.00.34
echo
echo Installation Von MeshDash SQL
echo 
echo "Das Zip File muss bereits im /home Verzeichnis des Aktuellen Users kopiert sein!"
echo
echo Der Raspberry muss Programme installieren und muss daher eine Internetverbindung haben!
echo
echo
echo Wenn ja, geht es sofort weiter, wenn nein, wird die Installation abgebrochen.
echo
######################################
read -r -p "Installation jetzt ausführen ja, oder nein? " A
if [ "$A" == "ja" ];then
	 echo "Führe Installation jetzt aus!"
elif [ "$A" == "nein" ];then
	   echo "Keine Installation. ENDE."
	   exit
else
		echo "Keine Installation. ENDE."
  	exit
fi
echo "OK, es geht weiter mit der Installation..."
sleep 2
#############Apt Install php, php-sqlite3, lighttpd
echo
echo Installiere jetzt weitere notwendige Software
echo
echo Installiere lighthttpd und PHP
echo
echo
sudo apt-get install lighttpd -f -y
clear
echo Als Dienst installieren, automatischer Start nach Reboot
echo
echo
sudo systemctl start lighttpd
sudo systemctl enable lighttpd
echo
echo Dienst für lighttpd wurde aktiviert wurde gestartet
sleep 3
clear
echo Installiere und konfiguriere PHP.
echo
echo
sudo apt-get install php-cgi php-fpm -y -f
sudo apt-get install php -y -f
sudo apt-get install php-sqlite3 -y -f
sudo apt-get install php-xml -f -y
sudo apt-get install php-zip -f -y
echo
echo
sudo lighty-enable-mod fastcgi
sudo lighty-enable-mod fastcgi-php
sudo systemctl restart lighttpd
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html
sudo systemctl restart lighttpd
echo
echo PHP und LIGHTTPD sind nun Installiert!
echo
echo
echo
echo Füge GPIO zur Gruppe www-data hinzu
echo
echo
sudo adduser www-data gpio
echo
#######################################
echo
echo Stoppe und Disable andere Services um Fehler zu vermeiden
######## Stop other running services
sudo systemctl stop allmeshcom.service
sudo systemctl stop checkmsg.service
sudo systemctl stop checkled.service
######## Disable other running services
sudo systemctl disable allmeshcom.service
sudo systemctl disable checkmsg.service
sudo systemctl disable checkled.service
echo
#######################################
###### Install Web-Application   ######
#######################################
hostIp=$(hostname -I | awk '{print $1}')
echo
echo Stoppe checkmh Service da neue Version ggf. kopiert wird
sudo systemctl stop checkmh.service
echo
echo Lösche meshdash Verzeichnis und erzeuge es neu
sudo rm -rf meshdash
sudo mkdir meshdash
echo
echo Kopiere Zipdateien in das meshdash Verzeichnis
sudo cp meshdash*.zip meshdash
echo
cd meshdash || exit
echo
echo Entpacke nun das zip Paket
sudo unzip meshdash*.zip
echo entferne das Zip Paket aus dem meshdash Verzeichnis
sudo rm meshdash*.zip
echo
echo Erzeuge Verzeichnis 5d in /var/www/html
sudo mkdir -p /var/www/html/5d
sudo chmod -R 755 /var/www/html/5d
echo
echo Kopiere nun die Daten in das Zielverzeichnis
echo
sudo cp -r ./* /var/www/html/5d/
sudo cp -r ./.htaccess /var/www/html/5d/
sudo cp -r ./.user.ini /var/www/html/5d/
echo
# Setzt alle .php-Dateien auf global 644 (r--)
sudo find /var/www/html/5d/ -type f -name "*.php" -exec chmod 644 {} \;
echo
# Setzt alle Verzeichnisse auf global 755 (r-x)
sudo find /var/www/html/5d/ -type d -exec chmod 755 {} \;
echo
# Setzt udp.pid auf 644. Not Halt für BG-Prozess udp_receiver.php
sudo chmod -R 644 /var/www/html/5d/udp.pid
# Setzt execute Verzeichnis auf 755 da hier die Ausführbaren Dateien sind für Keyword
sudo chmod -R 755 /var/www/html/5d/execute
#Setzte Owner und Gruppe für Web-Server im gesamten Verzeichnis
sudo chown -R www-data:www-data /var/www/html/5d
echo
echo Kopiere Dateien und setzte Rechte für Systemdienst checkmh.service
sudo chmod -R 755 script/checkmh.sh
sudo chmod -R 644 script/checkmh.service
sudo cp script/checkmh.service /etc/systemd/system/
sudo cp script/checkmh.sh ../meshdash/
echo
echo Aktiviere Systemdienst checkmh.service
sudo systemctl daemon-reload
sudo systemctl enable checkmh.service
sudo systemctl start checkmh.service
echo
echo FERTIG!
echo
echo "Starte nun Deinen Webbrowser und gib http://$hostIp ein."