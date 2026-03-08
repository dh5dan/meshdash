#!/bin/bash
echo "MeshDash Autostart-Uninstall-Script V 1.00.02"

SERVICE_NAME="meshdash-autostart"
SERVICE_FILE="/etc/systemd/system/${SERVICE_NAME}.service"

# Prüfen ob Script als root ausgeführt wird
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: Dieses Script muss als root oder mit sudo ausgeführt werden."
    exit 1
fi

echo "Removing MeshDash autostart service..."

# Service stoppen falls aktiv
if systemctl is-active --quiet $SERVICE_NAME; then
    systemctl stop $SERVICE_NAME
fi

# Service deaktivieren
if systemctl is-enabled --quiet $SERVICE_NAME; then
    systemctl disable $SERVICE_NAME
fi

# Service Datei löschen
if [ -f "$SERVICE_FILE" ]; then
    rm -f "$SERVICE_FILE"
fi

# systemd neu laden
systemctl daemon-reload

echo "Autostart wurde entfernt."