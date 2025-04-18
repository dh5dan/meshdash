#!/bin/bash
clear
echo "MeshDash Install-Script V 1.00.84"
echo
echo "Installation Von MeshDash-SQL"
echo 
echo "Das Zip File muss bereits im /home Verzeichnis des Aktuellen Users kopiert sein!"
echo
echo "Der Raspberry muss Programme installieren und muss daher eine Internetverbindung haben!"
echo
echo
echo "Wenn ja, geht es sofort weiter, wenn nein, wird die Installation abgebrochen."
echo
######################################
# Mit Instanzprüfung ob Chromium schon rennt.
# Logging eingebaut
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
##################################################
# Suche nach einer Datei mit dem Muster "meshdash-sql_*.zip"
if ls meshdash-sql_*.zip 1> /dev/null 2>&1; then
    echo
    echo "OK, es geht weiter mit der Installation..."
    sleep 2
else
    echo
    echo "Die Updatedatei wurde nicht gefunden!"
    echo "Bitte die Zip-Datei in das selbe Verzeichnis legen und install_5d.sh neu starten."
    exit
fi
##################################################
# Funktion, um das Verzeichnis /home/pi zu überprüfen
check_home_pi() {
    if [ "$(pwd)" == "/home/pi" ]; then
        # Wenn wir bereits im Verzeichnis /home/pi sind
        echo "Wir sind bereits im Verzeichnis /home/pi."
        return 0
    elif [ -d "/home/pi" ]; then
        # Wenn /home/pi existiert, aber nicht das aktuelle Verzeichnis ist
        echo "Das Verzeichnis /home/pi existiert und wir haben Schreibrechte."
        return 1
    else
        # Wenn /home/pi nicht existiert
        echo "Das Verzeichnis /home/pi existiert nicht. Wir werden es nun erstellen."
        sudo mkdir -p /home/pi
        if [ $? -eq 0 ]; then
            echo "Verzeichnis /home/pi erfolgreich erstellt."
            return 1
        else
            echo "Fehler beim Erstellen des Verzeichnisses /home/pi."
            exit 1
        fi
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
        cd /home/pi || exit
else
    copy_files
    echo "Haupt-Installation wird nun ausgeführt."
      echo "Wechseln ins Verzeichnis /home/pi..."
         cd /home/pi || exit
fi
############# Apt Install php, php-sqlite3, lighttpd
######## Stop other running services
echo
echo "Stoppe lighttpd wenn aktiv."
if systemctl is-active --quiet lighttpd.service; then
    sudo systemctl stop lighttpd.service
fi
sleep 3
echo
echo "Installiere jetzt weitere notwendige Software"
echo
echo "Installiere lighthttpd und PHP"
echo
echo
sudo apt-get install lighttpd -f -y
clear
echo "Als Dienst installieren, automatischer Start nach Reboot"
echo
echo
sudo systemctl enable lighttpd
echo
echo "Dienst für lighttpd wurde aktiviert"
clear
echo "Installiere und konfiguriere PHP."
echo
echo
sudo apt-get install php-cgi php-fpm -y -f
sudo apt-get install php -y -f
sudo apt-get install php-sqlite3 -y -f
sudo apt-get install php-xml -f -y
sudo apt-get install php-zip -f -y
sudo apt-get install unzip -f -y
sudo apt-get install lynx -f -y
sudo apt-get install php-curl -f -y
sudo apt-get install wget -f -y
echo
echo
sudo lighty-enable-mod fastcgi
sudo lighty-enable-mod fastcgi-php
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html
echo
echo "PHP und LIGHTTPD sind nun Installiert!"
echo
echo "Füge GPIO zur Gruppe www-data hinzu"
echo
if getent group gpio >/dev/null; then
    sudo adduser www-data gpio
fi
#######################################
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
#######################################
###### Install Web-Application   ######
#######################################
hostIp=$(hostname -I | awk '{print $1}')
echo
if systemctl is-active --quiet checkmh.service; then
  echo "Stoppe checkmh Service da neue Version ggf. kopiert wird"
  sudo systemctl stop checkmh.service
fi
echo
echo "Prüfe ob im /home/pi"
# Überprüfen, ob wir im /home/pi Verzeichnis sind
if [ "$(pwd)" != "/home/pi" ]; then
    echo "Nicht im Verzeichnis /home/pi. Wechseln..."
    cd /home/pi || exit  # Wechsel ins /home/pi Verzeichnis, falls nicht dort
fi
echo
echo "Lösche Meshdash-Verzeichnis und erzeuge es neu"
sudo rm -rf meshdash
sudo mkdir meshdash
echo
echo "Kopiere Zip-Dateien in das Meshdash-Verzeichnis"
sudo cp meshdash*.zip meshdash
echo
cd meshdash || exit
echo
echo "Entpacke nun das zip Paket"
sudo unzip meshdash*.zip
echo
echo "Entferne das Zip-Paket aus dem Meshdash-Verzeichnis"
sudo rm meshdash*.zip
echo
echo "Erzeuge Verzeichnis 5d in /var/www/html"
sudo mkdir -p /var/www/html/5d
sudo chmod -R 755 /var/www/html/5d
echo
echo "Kopiere nun die Daten in das HTML-Zielverzeichnis"
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
echo "Kopiere Systemdienst-Dateien und setzte Rechte für checkmh.service"
sudo chmod -R 755 script/checkmh.sh
sudo chmod -R 644 script/checkmh.service
sudo cp script/checkmh.service /etc/systemd/system/
sudo cp script/checkmh.sh ../meshdash/
echo
echo "Aktiviere Systemdienst checkmh.service"
sudo systemctl daemon-reload
sudo systemctl enable checkmh.service
sudo systemctl start checkmh.service
###############################################
#Räume auf
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
### Starte jetzt lighthttpd
echo "Starte Webserver und warte 3 Sekunden"
sudo systemctl start lighttpd
sleep 3
echo
######################################
# Alle Cronjobs für den Benutzer www-data löschen
sudo crontab -u www-data -r
echo "Alle Cronjobs von www-data wurden gelöscht."
#########################################
# Ready fpr Take-Off
echo FERTIG!
echo
echo "Starte nun Deinen Webbrowser und gib http://$hostIp/5d ein."
echo