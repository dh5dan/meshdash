#!/bin/bash
echo "MeshDash Autostart-Install-Script V 1.00.04"

SERVICE_NAME="meshdash-autostart"
SERVICE_FILE="/etc/systemd/system/${SERVICE_NAME}.service"
SCRIPT_PATH="/var/www/html/5d/script/autostart.php"

# Prüfen ob Script als root ausgeführt wird
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: Dieses Script muss als root oder mit sudo ausgeführt werden."
    exit 1
fi

# Prüfen ob Script existiert
if [ ! -f "$SCRIPT_PATH" ]; then
    echo "ERROR: Script not found: $SCRIPT_PATH"
    exit 1
fi

# Webserver erkennen
WEBSERVER=""
if systemctl is-active --quiet apache2; then
    WEBSERVER="apache2.service"
elif systemctl is-active --quiet httpd; then
    WEBSERVER="httpd.service"
elif systemctl is-active --quiet nginx; then
    WEBSERVER="nginx.service"
elif systemctl is-active --quiet lighttpd; then
    WEBSERVER="lighttpd.service"
else
    echo "ERROR: Kein unterstützter Webserver aktiv. Installation abgebrochen."
    exit 1
fi

echo "Gefundener Webserver: $WEBSERVER"

echo "Installing MeshDash autostart service..."

# Service Datei erstellen
cat <<EOF > $SERVICE_FILE
[Unit]
Description=MeshDash Autostart
After=$WEBSERVER network-online.target
Requires=$WEBSERVER

[Service]
Type=oneshot
User=www-data
ExecStart=/usr/bin/curl -fsS http://127.0.0.1/5d/script/autostart.php
TimeoutStartSec=60
RemainAfterExit=false
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

# Rechte setzen
chmod 644 $SERVICE_FILE

# systemd neu laden
systemctl daemon-reload

# Service aktivieren
systemctl enable $SERVICE_NAME

echo ""
echo "Autostart installiert."
echo "Script wird beim nächsten Systemstart automatisch ausgeführt."