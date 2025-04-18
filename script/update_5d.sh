#!/bin/bash
clear
echo "MeshDash Update-Script V 1.00.44"
echo
echo "UPDATE einer bestehenden MeshDash SQL Installation."
echo "Es wird nur das MeshDash installiert,"
echo "kein PHP oder sonstige Tools."
echo "Zur installation der nötigen Tools wie PHP, Webbrowser bitte die install.sh ausführen."
echo
echo "Das NEUE Zip File muss bereits im /home Verzeichnis des aktuellen Users kopiert sein!"
echo
echo
echo "Wenn ja, geht es sofort weiter, wenn nein, wird das Update abgebrochen."
echo
######################################
read -r -p "Update jetzt ausführen ja, oder nein? " A
if [ "$A" == "ja" ];then
	 echo "Führe Update jetzt aus!"
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
# Funktion, um das Verzeichnis /home/pi zu überprüfen
check_home_pi() {
    if [ "$(pwd)" == "/home/pi" ]; then
        # Wenn wir bereits im Verzeichnis /home/pi sind
        echo "Wir sind bereits im Verzeichnis /home/pi."
        return 0
    elif [ -d "/home/pi" ]; then
        # Wenn /home/pi existiert, aber nicht das aktuelle Verzeichnis ist
        echo "Das Verzeichnis /home/pi existiert."
        return 1
    else
        # Wenn /home/pi nicht existiert
        echo "Es muss einmalig Install ausgeführt werden!"
        echo "Neue Updates werden dann nur noch über MeshDash oder alternativ mit dem Update Skript installiert."
        exit 1
    fi
}

# Dateien, die kopiert werden sollen
FILE1="install_5d.sh"
FILE2="update_5d.sh"
ZIPFILE=$(ls *.zip)

# Funktion zum Kopieren der Dateien
copy_files() {

    echo "Kopiere Dateien nach /home/pi..."

    sudo cp "$FILE1" /home/pi/
    sudo cp "$FILE2" /home/pi/

    # Überprüfen, ob eine .zip Datei existiert
    if [ -z "$ZIPFILE" ]; then
        echo "Keine .zip Datei gefunden."
        exit 1
    else
        sudo cp "$ZIPFILE" /home/pi/
    fi

    if [ $? -eq 0 ]; then
        echo "Dateien erfolgreich kopiert."
    else
        echo "Fehler beim Kopieren der Dateien."
        exit 1
    fi
}

# Hauptlogik
check_home_pi
if [ $? -eq 0 ]; then
    echo "Installationsvorgang kann fortgesetzt werden."
      echo "Wechseln ins Verzeichnis /home/pi..."
        cd /home/pi
else
    copy_files
    echo "Haupt-Installation wird nun ausgeführt."
      echo "Wechseln ins Verzeichnis /home/pi..."
         cd /home/pi
fi
######################################
echo
echo "Stoppe und Disable andere Services um Fehler zu vermeiden"
######## Stop other running services
if systemctl is-active --quiet allmeshcom.service; then
    sudo systemctl stop allmeshcom.service
    sudo systemctl disable allmeshcom.service
fi
if systemctl is-active --quiet checkmsg.service; then
    sudo systemctl stop checkmsg.service
    sudo systemctl disable checkmsg.service
fi
if systemctl is-active --quiet checkled.service; then
    sudo systemctl stop checkled.service
    sudo systemctl disable checkled.service
fi
echo
######################################
###### Update Web-Application   ######
######################################
hostIp=$(hostname -I | awk '{print $1}')
echo
if systemctl is-active --quiet checkmh.service; then
  echo "Stoppe checkmh Service da neue Version ggf. kopiert wird"
  sudo systemctl stop checkmh.service
fi
echo
echo "Lösche meshdash Verzeichnis und erzeuge es neu"
sudo rm -rf meshdash
sudo mkdir meshdash
echo
echo "Erzeuge Verzeichnis für Systemdienst checkmh.service"
sudo mkdir meshdash
echo
echo "Kopiere Zip-Dateien in das Meshdash-Verzeichnis"
sudo cp meshdash*.zip meshdash
echo
cd meshdash || exit
echo
echo "Entpacke nun das Zip-Paket"
sudo unzip meshdash*.zip
echo
echo "Entferne das Zip Paket aus dem meshdash Verzeichnis"
sudo rm meshdash*.zip
echo
echo "Erzeuge Verzeichnis 5d in /var/www/html"
sudo mkdir -p /var/www/html/5d
sudo chmod -R 755 /var/www/html/5d
echo
echo "Kopiere nun die Daten in das Zielverzeichnis"
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
echo "Kopiere Dateien und setzte Rechte für Systemdienst checkmh.service"
sudo chmod -R 755 script/checkmh.sh
sudo chmod -R 644 script/checkmh.service
sudo cp script/checkmh.service /etc/systemd/system/
sudo cp script/checkmh.sh ../meshdash/
echo
echo "Aktiviere Systemdienst checkmh.service"
sudo systemctl daemon-reload
sudo systemctl enable checkmh.service
sudo systemctl start checkmh.service
echo
###############################################
#Räume auf
echo
echo "Prüfe ob noch im /home/pi"
# Überprüfen, ob wir im /home/pi Verzeichnis sind
if [ "$(pwd)" != "/home/pi" ]; then
    echo "Nicht im Verzeichnis /home/pi. Wechseln..."
    cd /home/pi || exit  # Wechsel ins /home/pi Verzeichnis, falls nicht dort
fi
echo
echo "Entferne das Zip-Paket aus dem \home\pi"
sudo rm meshdash*.zip
echo
###############################################
echo
echo FERTIG!
echo
echo "Starte nun Deinen Webbrowser und gib http://$hostIp/5d ein."