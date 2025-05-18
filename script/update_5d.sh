#!/bin/bash
clear
echo "MeshDash Update-Script V 1.00.46"
echo
echo "UPDATE einer bestehenden MeshDash SQL Installation."
echo "Es wird nur das MeshDash installiert, kein PHP oder sonstige Tools."
echo "Zur Installation der nötigen Tools wie PHP, Webbrowser bitte die install_5d.sh ausführen."
echo
echo "Falls sich kein ZIP-Archiv im Home-Verzeichnis des aktuellen Benutzers befindet,"
echo "wird im nächsten Schritt angeboten, die aktuelle Release-Version von GitHub herunterzuladen."
echo "Eine Internetverbindung ist nur erforderlich, wenn der Download der Release online erfolgen soll."
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
##################################################
# Suche nach einer Datei mit dem Muster "meshdash-sql_*.zip"
if ls meshdash-sql_*.zip 1> /dev/null 2>&1; then
    echo
    echo "OK, es geht weiter mit der Installation..."
    sleep 2
else
   echo
       echo "Keine Meshdash-Zip Datei gefunden."

       # Frage ob Online-Download gewünscht ist
       read -r -p "Soll die neueste Version von GitHub jetzt heruntergeladen werden? (ja/nein) " downloadChoice
       if [ "$downloadChoice" != "ja" ]; then
           echo "Installation abgebrochen."
           exit 1
       fi

       echo "Hole neueste Release-Infos von GitHub..."
       echo
       # API-Abfrage und Extrahieren der Download-URL für das meshdash*.zip Archiv
       downloadUrl=$(curl -s "https://api.github.com/repos/dh5dan/meshdash/releases/latest" \
         | grep "browser_download_url" \
         | grep "meshdash-sql.*\.zip" \
         | head -n 1 \
         | cut -d '"' -f 4)

       if [ -z "$downloadUrl" ]; then
           echo "Fehler: Keine passende ZIP-Datei im neuesten Release gefunden."
           exit 1
       fi

       echo "Lade Datei herunter: $downloadUrl"
       filename="${downloadUrl##*/}"   # schneidet alles vor dem letzten Slash weg, bleibt nur der Dateiname
       wget --show-progress -q -O "$filename" "$downloadUrl"

       if [ $? -ne 0 ]; then
           echo "Fehler beim Herunterladen der ZIP-Datei."
           exit 1
       fi

       echo
       echo "Download abgeschlossen."
fi
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

     if [ -f "$FILE1" ]; then
          sudo cp "$FILE1" /home/pi/
     fi

     if [ -f "$FILE2" ]; then
          sudo cp "$FILE2" /home/pi/
     fi

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
echo "Stoppe lighttpd wenn aktiv."
if systemctl is-active --quiet lighttpd.service; then
    sudo systemctl stop lighttpd.service
fi
sleep 3
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
echo "Kopiere nun die Daten in das HTML-Zielverzeichnis"
sudo cp -r ./* /var/www/html/5d/
sudo cp -r ./.htaccess /var/www/html/5d/
sudo cp -r ./.user.ini /var/www/html/5d/
# Setzt alle .php-Dateien auf global 644 (r--)
sudo find /var/www/html/5d/ -type f -name "*.php" -exec chmod 644 {} \;
# Setzt alle Verzeichnisse auf global 755 (r-x)
sudo find /var/www/html/5d/ -type d -exec chmod 755 {} \;
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
### Starte jetzt lighthttpd
echo "Starte Webserver und warte 3 Sekunden"
sudo systemctl start lighttpd
sleep 3
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
echo "Starte nun den Webbrowser und gib http://$hostIp/5d ein."