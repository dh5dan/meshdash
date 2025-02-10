#!/bin/bash
clear
echo MeshDash Update-Script V 1.00.20
echo
echo UPDATE einer bestehenden MeshDash Installation @5D.
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
sudo chmod -R 645 /var/www/html/5d
echo
echo Installiere nun das Update
echo
sudo cp -r ./* /var/www/html/5d/
echo
echo
# Setzt alle .php-Dateien auf global 644 (r--)
sudo find /var/www/html/5d/ -type f -name "*.php" -exec chmod 644 {} \;
echo
# Setzt alle Verzeichnisse auf global 645 (r-x)
sudo find /var/www/html/5d/ -type d -exec chmod 645 {} \;
echo
# Setzt speziell database und log auf 647 damit Dateien darin erzeugt werden dürfen
sudo chmod -R 647 /var/www/html/5d/database
sudo chmod -R 647 /var/www/html/5d/log
sudo chmod -R 647 /var/www/html/5d/execute
echo
# Setzt udp.pid auf 644
sudo chmod -R 644 /var/www/html/5d/udp.pid
echo
echo FERTIG!
echo
echo
echo
echo "Starte nun Deinen Webbrowser und gib http://$hostIp ein."
echo
echo
