#!/bin/bash
clear
echo MeshDash Update-Script V 1.00.30
echo
echo UPDATE einer bestehenden MeshDash SQL Installation.
echo Es wird nur das MeshDash installiert,
echo kein PHP oder sonstige Tools.
echo Zur installation der nötigen Tools wie PHP, Webbrowser bitte die install.sh ausführen.
echo
echo "Das NEUE Zip File muss bereits im /home Verzeichnis des aktuellen Users kopiert sein!"
echo
echo
echo Wenn ja, geht es sofort weiter, wenn nein, wird das Update abgebrochen.
echo

read -r -p "Update jetzt ausführen ja, oder nein? " A
if [ "$A" == "ja" ];then
	echo "Führe Update jetzt aus."
elif [ "$A" == "nein" ];then
	echo "Kein Update. ENDE."
	exit
else
		echo "Kein Update. ENDE."
  	exit
fi

echo "OK, es geht weiter mit dem Update..."
sleep 2

######################################
###### Update Web-Application   ######
######################################
hostIp=$(hostname -I | awk '{print $1}')

echo Lösche meshdash Verzeichnis und erzeuge es neu
sudo rm -rf meshdash
sudo mkdir meshdash
echo
echo
echo Kopiere Zipdatei in das meshdash Verzeichnis
sudo cp meshdash*.zip meshdash
echo
cd meshdash || exit

echo Entpacke nun das zip Packet
sudo unzip meshdash*.zip
echo entferne das Zip Packet aus dem meshdash Verzeichnis
sudo rm meshdash*.zip
echo
echo Erzeuge Verzeichnis 5d in /var/www/html
sudo mkdir -p /var/www/html/5d
sudo chmod -R 755 /var/www/html/5d
echo
echo Installiere nun das Update
echo
sudo cp -r ./* /var/www/html/5d/
sudo cp -r ./.htaccess /var/www/html/5d/
sudo cp -r ./.user.ini /var/www/html/5d/
echo
echo
# Setzt alle .php-Dateien auf global 644 (r--)
sudo find /var/www/html/5d/ -type f -name "*.php" -exec chmod 644 {} \;
echo
# Setzt alle Verzeichnisse auf global 755 (r-x)
sudo find /var/www/html/5d/ -type d -exec chmod 755 {} \;
echo
echo
# Setzt udp.pid auf 644. Not Halt für BG-Prozess udp_receiver.php
sudo chmod -R 644 /var/www/html/5d/udp.pid
# Setzt execute Verzeichnis auf 755 da hier die Ausführbaren Dateien sind für Keyword
sudo chmod -R 755 /var/www/html/5d/execute
#Setzte Owner und Gruppe für Web-Server im gesamten Verzeichnis
sudo chown -R www-data:www-data /var/www/html/5d
echo
echo FERTIG!
echo
echo
echo
echo "Starte nun Deinen Webbrowser und gib http://$hostIp ein."
echo
echo
