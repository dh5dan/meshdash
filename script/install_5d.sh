#!/bin/bash
clear
echo "MeshDash Install-Script mit Headless-Browser V 1.00.55"
echo
echo "Installation Von MeshDash SQL"
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
echo "OK, es geht weiter mit der Installation..."
sleep 2

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
sudo systemctl start lighttpd
sudo systemctl enable lighttpd
echo
echo "Dienst für lighttpd wurde aktiviert wurde gestartet"
sleep 3
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
echo
echo
sudo lighty-enable-mod fastcgi
sudo lighty-enable-mod fastcgi-php
sudo systemctl restart lighttpd
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html
sudo systemctl restart lighttpd
echo
echo "PHP und LIGHTTPD sind nun Installiert!"
echo
echo "Füge GPIO zur Gruppe www-data hinzu"
echo
echo
if getent group gpio >/dev/null; then
    sudo adduser www-data gpio
fi
echo
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
####### Installiere Node.js
echo
echo "Installiere Node.js"
sudo apt install -y nodejs npm

#####################################
# Setze den Projektordnerpfad und die package.json
PROJECT_DIR="/home/pi/puppeteer_project"
PACKAGE_JSON="$PROJECT_DIR/package.json"

# Überprüfe, ob das Projektverzeichnis und die package.json existieren
if [ ! -d "$PROJECT_DIR" ] || [ ! -f "$PACKAGE_JSON" ]; then
    # Wenn das Verzeichnis oder die package.json nicht existiert
    echo
    echo "Installiere Puppeteer"
    echo
    sudo mkdir -p "$PROJECT_DIR"
    cd "$PROJECT_DIR" || exit
    sudo npm init -y  # Erstellt eine package.json
    sudo npm install puppeteer
    cd .. || exit
else
    echo "Puppeteer-Projekt und package.json existieren bereits. Überspringe Erstellungs-/ Installationsvorgang."
fi
echo
echo
echo "Installiere Chromium-Browser"
sudo apt install -y chromium-browser
echo
echo "Installiere PM2-Taskmanager"
sudo npm install -g pm2
echo
sudo npm install -g moment-timezone
echo
###################################################
# Erzeuge Skript für Browser-Headless Betrieb
# Zielpfad für das Puppeteer-Skript
echo "Prüfe ob run_puppeteer.js existiert und wenn nicht dann erzeugen"
file_path="/home/pi/puppeteer_project/run_puppeteer.js"

# Überprüfen, ob die Datei schon existiert
if [ ! -f "$file_path" ]; then
    # Skript-Inhalt erstellen und in die Datei schreiben (mit sudo)
    echo 'const puppeteer = require("puppeteer");
          const moment = require("moment-timezone");  // Importiere moment-timezone
          const fs = require("fs");
          const { exec } = require("child_process");

          let browser;
          let page;

          // Log-Datei definieren
          const logFile = "/home/pi/chrome_logfile.log"; // Passe den Pfad zur Log-Datei an

          // Funktion, um Logs in die Datei zu schreiben
          function logToFile(message) {
              const timestamp = moment().tz("Europe/Berlin").format("YYYY-MM-DD HH:mm:ss");  // MESZ, berücksichtigt Sommerzeit
              fs.appendFileSync(logFile, `${timestamp} - ${message}\n`, "utf8");
          }

          async function startBrowser() {
              try {
                  exec("pgrep chromium", async (error, stdout, stderr) => {
                      if (!error && stdout) {
                          logToFile("Chromium läuft bereits.");

                          // Zusätzliche Prüfung: Schläft Chromium?
                          exec("ps -eo comm,state | grep chromium", (psError, psStdout, psStderr) => {
                              if (psError || psStderr) return;

                              const lines = psStdout.trim().split("\n");
                              const allSleeping = lines.every(line => line.includes(" S"));

                              if (allSleeping) {
                                  logToFile("Alle Chromium-Instanzen schlafen. Beende und starte neu...");
                                  exec("killall chromium", (killError) => {
                                      if (killError) {
                                          logToFile(`Fehler beim Beenden von Chromium: ${killError.message}`);
                                          return;
                                      }
                                      launchNewChromium(); // Starte eine neue Instanz
                                  });
                              }
                          });

                          return;
                      }

                      launchNewChromium(); // Falls Chromium nicht läuft, neue Instanz starten
                  });

              } catch (error) {
                  logToFile(`Fehler beim Starten von Chromium: ${error.message}`);
              }
          }

          async function launchNewChromium() {
              logToFile("Starte neue Chromium-Instanz...");

              browser = await puppeteer.launch({
                  headless: true, // Headless-Modus
                  executablePath: "/usr/bin/chromium-browser", // Pfad zum lokalen Chromium eintragen
                  args: ["--no-sandbox", "--disable-setuid-sandbox"]
              });

              page = await browser.newPage();
              await page.goto("http://localhost/5d", { waitUntil: "networkidle2" });
              logToFile("Chromium gestartet.");

              // Lässt das Skript weiterlaufen, um setInterval aktiv zu halten
              await new Promise(() => {});
          }

          async function monitorBrowser()
          {
              setInterval(async () => {
                  // Prüfe, ob Chromium läuft und ob alle Instanzen schlafen
                  exec("ps -eo comm,state | grep chromium", (error, stdout, stderr) => {
                      if (error || stderr || !stdout.includes("chromium")) {
                          logToFile("Chromium ist abgestürzt oder nicht aktiv, Neustart...");
                          restartBrowser();
                          return;
                      }

                      // Prüfe, ob ALLE Chromium-Instanzen im Schlafmodus (S) sind
                      const lines = stdout.trim().split("\n");
                      const allSleeping = lines.every(line => line.includes(" S"));

                      if (allSleeping) {
                          logToFile("Alle Chromium-Instanzen schlafen. Neustart...");
                          exec("killall chromium", (killError) => {
                              if (killError) {
                                  logToFile(`Fehler beim Beenden von Chromium: ${killError.message}`);
                              }
                              restartBrowser();
                          });
                      }
                  });

                  // Check if die Seite noch existiert
                  if (!page || page.isClosed()) {
                      logToFile("Seite ist nicht mehr offen, neu laden...");
                      try {
                          page = await browser.newPage();
                          await page.goto("http://localhost/5d", { waitUntil: "networkidle2" });
                          logToFile("Seite wurde neu geladen.");
                      } catch (error) {
                          logToFile(`Fehler beim Neuladen der Seite: ${error.message}`);
                      }
                  }
              }, 5000); // Überprüfe alle 5 Sekunden
          }

          async function restartBrowser()
          {
              if (browser)
              {
                  try {
                      await browser.close(); // Schließe die aktuelle Instanz
                      logToFile("Chromium-Instanz wurde geschlossen.");
                  } catch (error) {
                      logToFile(`Fehler beim Schließen der Instanz: ${error.message}`);
                  }
              }

              startBrowser(); // Starte eine neue Instanz
          }

          // Initial Start
          startBrowser();
          logToFile("Starte Monitoring alle 5 Sekunden.");
          monitorBrowser();' | sudo tee "$file_path" > /dev/null
    echo "Das Puppeteer-Skript wurde erfolgreich erstellt: $file_path"
else
    echo "Das Puppeteer-Skript existiert bereits: $file_path"
fi

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
echo "Lösche meshdash Verzeichnis und erzeuge es neu"
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
# Setzte Task
echo "Setze Task und starte Headless-Browser-Skript"
# Name des PM2-Prozesses
process_name="puppeteer-task"
# Überprüfen, ob der Prozess bereits läuft
if sudo pm2 list | grep -q "$process_name"; then
    # Prozess existiert, also neu starten mit der -f Option und Umgebungsvariablen aktualisieren
    sudo pm2 restart "$process_name" --force --update-env
    echo "Prozess $process_name wurde neu gestartet."
else
    # Prozess existiert nicht, also starten
    sudo pm2 start "$file_path" --name "$process_name"
    echo "Prozess $process_name wurde gestartet."
fi
echo
# Cronjob definieren
CRON_JOB="*/5 * * * * /usr/local/bin/pm2 resurrect"
echo "Prüfen, ob der Cronjob bereits in der Crontab von root existiert"
echo "Wenn nicht, trage Task-Start in Cron ein mit 5min Prüfintervall"
(crontab -l 2>/dev/null | grep -F "$CRON_JOB") || (echo "$CRON_JOB" | sudo crontab -)
echo
######################################
# Ready fpr Take-Off
echo FERTIG!
echo
echo "Starte nun Deinen Webbrowser und gib http://$hostIp/5d ein."