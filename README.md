# MeshDash-SQL

## Was ist MeshDash-SQL?

MeshDash-SQL ist eine webbasierte Client-Anwendung zur Anzeige und Verwaltung von Nachrichten  
auf Geräten mit der Firmware MeshCom.  
Die Datenverarbeitung und -speicherung erfolgt über SQLite3, daher der Zusatz "SQL".

Dieses Projekt basiert ursprünglich auf der Arbeit von DL4QB.  
Mit seiner tatkräftigen Unterstützung wurde daraus dieses Projekt entwickelt.  
Es handelt sich um ein Freizeit-Hobbyprojekt, das die Anwendung und Bedienung von MeshCom  
auf eine webbasierte Plattform bringen soll.

![RELEASE](/docs/front_menu.jpg)

MeshDash-SQL wurde primär für den Einsatz auf einem Raspberry Pi mit Linux entwickelt.  
Es ist aber auch auf jedem Debian-OS in einer VM bis einschliesslich Trixie und sogar Docker lauffähig.  
Unter Windows ist MeshDash auch lauffähig mit etwas fundierten kenntnissen über Apache/PHP.   
PHP (mindestens Version 7.4) ist nötig.  
Benötigt werden u.a. die PHP-Module: socket, sqlite3, curl, zip und unzip.  

[***Zu MeshDash auf Docker gibt es im letzten Abschnitt weitere Infos.***](#meshdash-auf-docker)

### ⚠️ Hinweis
Die Unterstützung für **PHP 7.4** wird in zukünftigen Versionen entfallen (deprecated).   
Eine Umstellung auf **PHP >= 8.x** ist daher empfohlen.

---

### Einige Highlight-Funktionen von MeshDash:
- Echtzeit-Anzeige der Nachrichten
- Speicherung und Archivierung der Daten mittels SQLite3-Datenbank
- [Anlage individuelle Filtergruppen](#groups)
- [Ausführung von Skripten via Keywords](#keywords)
- Remote Mheard-Abfragen
- [Soundbenachrichtigungen für eigene Nachrichten](#notification)
- [Update über die Weboberfläche inkl. Download](#update)
- [Restore-Funktion eines zuvor angelegten Backups](#restore-funktion-eines-zuvor-angelegten-backups)
- [Senden von Befehlen an den Node](#send-command)
- Sensorabfragen mit Schwellwertbenachrichtigung
- [Senden von Nachrichten über eine Send-Queue mit festen Intervallen.](#send-queue)  
  Dies gewährleistet, das aufeinanderfolgende Nachrichten mit einem  
  eingestellten Zeitabstand gesendet werden, um Sendekollisionen zu vermeiden.
- [Anzeige via Byte-Counter über die verbleibende Anzahl von Zeichen pro Nachricht.](#byte-counter-anzeige)
- [Auswahl gängiger Emoji über ein DropDown zum Einfügen in die Nachricht.](#emoji-einfügen)
- [Zusätzliche Kennzeichnung neuer Nachrichten mit Hintergrundfarbe und Sound.](#tab-benachrichtigungen-bei-neuen-nachrichten)
- [Suchfunktion nach Auswahlkriterien über die gesamte Nachrichtendatenbank möglich.](#suchfunktion-über-die-gesamte-nachrichtendatenbank)
- [Grafische Anzeige der lokalen MHEARD mit Open-Street-Map](#grafische-anzeige-der-mheard)
- [Darstellung der Nachrichten im Bubble-Style](#die-ansicht-kann-nun-auch-im-bubble-Style-dargestellt-werden)
- [HF-Reichweitentest zur Analyse der Funkabdeckung + Remote-Aktivierung der Bake via OTP](#hf-reichenweitentest-via-intervall-baken) 
- Upload von Sound-/Skripten für Benachrichtigungen und Keyword-Aktionen
- Dyn. erweiterbare Call-/Keyword-Definitionen (keine Feldbegrenzung mehr)
- [Unterstützung der Anzeige auf mobilen Geräten Smartphone/Tablett](#mobile-ansicht-auf-einem-smartphone-)
- [Call-basierte Notizfunktion](#notizfunktion-für-calls)
- [Mehrsprachenunterstützung (DE/EN/FR/ES/NL/IT)](#mehrsprachenunterstützung-von-meshdash)
- [UDP-Datagramm Weiterleitung](#hier-noch-ein-paar-bilder-aus-den-menüpunkten)
- [MeshDash in Docker-Umgebung](#meshdash-auf-docker)
  
Sollten Probleme auftreten, bitte im GitHub-Issue-Bereich melden – falls das Problem  
noch nicht dort beschrieben ist.
Es gibt außerdem eine Telegram-Gruppe zu MeshDash.  
Den Link zur Einladung senden wir auf Anfrage gerne zu.

---

## Wo finde ich die Releases?

Die aktuellen Releases von MeshDash findest du auf der GitHub-Seite unter dem Reiter „Releases“:    
https://github.com/dh5dan/meshdash/releases

Dort stehen jeweils die aktuellen Versionen als ZIP-Dateien zum Download bereit.

### Hier ist "Latest" anzuklicken!
![RELEASE](/docs/release.jpg)

🔴 **Wichtiger Hinweis für neue Benutzer:**

Die Dateien befinden sich unter **Assets** – bitte darauf klicken, um die Liste auszuklappen!

![ASSETS](/docs/assets.jpg)

---

### In MeshDash ist nun ein Abruf der aktuellen Release-Version möglich.
#### Die aktuelle Version kann direkt über MeshDash geladen werden, um auf dem neuesten Stand zu bleiben.
#### Der Changelog ist ebenfalls direkt abrufbar.
![FILES](/docs/update_menu.jpg)

#### Hier wird das Changelog in einem Dialog-Fenster angezeigt.
![FILES](/docs/50_md_release_version.jpg)

**Für die Installation sind nur folgende Dateien relevant:**  
Hier mal ein Beispiel:
- meshdash-sql_Vx.xx.xx.zip - Das Hauptprogramm
- install.sh - Installationsskript (bei Neuinstallation)
- update_5d.sh - Update-Skript - (optional, da Webupdate möglich)
- Manual-MeshDas-SQL.pdf - Ausführliches Benutzerhandbuch

![FILES](/docs/files.jpg)

---

### Restore-Funktion eines zuvor angelegten Backups
MeshDash-SQL bietet die Möglichkeit, zuvor erstellte Backups wiederherzustellen.  
So kannst du bei Problemen oder nach einem Update den vorherigen Zustand einfach zurückspielen.  

Wichtig:   
Die Datensicherheit ist nur gewährleistet, wenn auch die Datenbank mitgesichert wurde.  
Der Restore erfolgt über das Webinterface oder manuell, je nach Bedarf.
![FILES](/docs/restore.jpg)

---

### Die Ansicht kann nun auch im Bubble-Style dargestellt werden
Das Layout zeigt Nachrichten nun als Sprechblasen, ähnlich wie bei Messenger-Apps,  
was für eine bessere Übersicht und ein moderneres Design sorgt.
![FILES](/docs/bubble_style_view.jpg)

---

## Was steht in der Manual-Mesh-Dash-SQL.pdf?

Die frühere Kurzanleitung.pdf wurde durch eine umfassende Anleitung ersetzt: die Manual-Mesh-Dash-SQL.pdf.

Diese enthält:

- Eine Installationsanleitung mit den wichtigsten Schritten zur Einrichtung von MeshDash.
- Ein detailliertes Benutzerhandbuch mit Screenshots der Weboberfläche und Erläuterungen zu den Menüpunkten.
- Einen Troubleshooting-Guide mit Tipps zur Fehleranalyse.

Das Manual unterstützt dabei, MeshDash schnell zu installieren und effektiv zu nutzen.  
Es wird regelmäßig aktualisiert.

---

## Hier noch ein paar Bilder aus den Menüpunkten:

![FILES](/docs/01_config.jpg)

<a id="send-queue"></a>
![FILES](/docs/01_1_send_queue.jpg)

<a id="notification"></a>
![FILES](/docs/02_alert.jpg)

<a id="keywords"></a>
![FILES](/docs/03_keyword.jpg)

<a id="update"></a>
![FILES](/docs/04_update.jpg)

<a id="groups"></a>
![FILES](/docs/10_groups.jpg)

<a id="mheard"></a>
![FILES](/docs/20_mheard.jpg)

<a id="send-command"></a>
![FILES](/docs/30_send_command.jpg)

![FILES](/docs/31_send_command.jpg)

---

### Mehrsprachenunterstützung von MeshDash
MeshDash ist nun in 6 Sprachen verfügbar:  
Deutsch, Englisch, Französisch Spanisch, Niederländisch und Italienisch.  
Die Übersetzungen wurde mit Hilfe einer KI erstellt und können ggf. noch unvollständig sein.  
In Meshdash besteht jedoch die Möglichkeit, diese Übersetzungen individuell anzupassen.

![FILES](/docs/language.jpg)

![FILES](/docs/language_edit_menu.jpg)

![FILES](/docs/language_edit.jpg)

---

### Debug-Informationen sofort auf einen Blick
Ideal zur Fehleranalyse als Screenshot.  
Hier können außerdem unkompliziert Logdateien heruntergeladen werden,  
die zusätzliche hilfreiche Informationen liefern.

![FILES](/docs/33_debug_info.jpg)

![FILES](/docs/33_debug_info_logs.jpg)

---

### Byte-Counter Anzeige
Die Byte-Anzeige berücksichtigt das UTF-8-Format,  
da UTF-8-Zeichen mehr als 1 Byte belegen können.  
Die maximale Textlänge ist aktuell auf 150 Zeichen begrenzt.

![FILES](/docs/34_msg_byte_counter.jpg)

---

### Emoji einfügen
Über ein Drop-Down-Menü kann aus einer Auswahl gängiger Emojis gewählt werden.
![FILES](/docs/32_send_command.jpg)

---

### Suchfunktion über die gesamte Nachrichtendatenbank
Über das Lupen-Symbol wird der Suchdialog geöffnet, in dem verschiedene Suchkriterien ausgewählt werden können.
![FILES](/docs/search_01.jpg)

---

#### Datums- und Zeit-Dialogfenster.
Ein Klick auf ein Datumsfeld öffnet einen Datums- und Zeit-Dialog zur einfachen Auswahl.
![FILES](/docs/search_02.jpg)

---

### Tab-Benachrichtigungen bei neuen Nachrichten.
Die Hintergrundfarbe der Tabs sowie Soundbenachrichtigungen lassen sich individuell pro Tab-Gruppe einstellen.
![FILES](/docs/60_desktop_tab_alert.jpg)

---

### HF-Reichenweitentest via Intervall-Baken.
Wir können über Intervall-Baken nun auch einen HF-Reichweitentest ausführen.  
Einstellbar sind Intervallzeiten/Stop-Count sowie ein Freitext.  
Gesendet wird im Präfix der Counter wie auch die eingestellte Intervallzeit.  
Die Bake kann auch aus der Ferne über ein OTP (One-Time-Password) gestartet werden. 
![FILES](/docs/set_beacon.jpg)
![FILES](/docs/msg_beacon.jpg)

---

### Notizfunktion für Calls.
![FILES](/docs/call_notice.jpg)

---

### Grafische Anzeige der MHEARD
![FILES](/docs/mheard_osm.jpg)

---

### Mobile Ansicht auf einem Smartphone 
![FILES](/docs/61_handy_Tab_alert.jpg)

---

## MeshDash auf Docker
Es ist möglich MeshDash auch in einer Docker-Umgebung laufen zu lassen.  
Ich habe dies experimentell mal auf einer Synology DS1522+ eingerichtet.  
Zum Einsatz kommen 2 Image-Container: PHP + nginx (Webserver).   
Version nginx: nginx:1.29.1-bookworm-perl  
Version php  : php:8.4.12-fpm-trixie  

#### Es gibt 3 Mountpoints:
- html   (für den Webinhalt)
- conf.d (für die nginx konfiguration)
- logs   (Logfile des nginx Webserver. Optional aber vielleicht von Interesse) 

#### Port-Mapping:  
Nginx: 8080:80        host:container/protokoll  (tcp ist default)  
PHP:   1799:1799/udp  host:container/protokoll

#### Es gibt insgesamt 3 Dateien die zum Einsatz kommen.   
- docker-compose.yml (baut den eigentlichen Docker Container mit allen Einstellungen)    
- Dockerfile     (baut ein Docker-PHP Image mit zusätzlichen Modulen socket,zip etc..)  
- default.conf   (Konfigurationsdatei für den nginx Webserver)  

Je nach System kann der Name der "docker-compose.yml" anders heißen.  
Synology nennt sie zwar "docker-compose.yml" speichert sie aber als "compose.yaml" ab.    
Das "Dockerfile" welches die Information zum Bauen des PHP-Images beinhaltet,  
liegt bei mir in einem Sub-verzeichnis "php-addon".    
Man kann es aber auch auf einer Ebene (./) legen. Hier muss man dann aber bei   
"build" den Pfad herausnehmen.

Die Quell-Pfade für Mountpoints müssen an die persönlichen lokalen  
Gegebenheiten angepasst werden. Auch ist es wichtig die jeweiligen  
Datei-/Verzeichnisrechte korrekt zu setzten, damit Docker auch schreiben/Lesen kann.    
Das ist eines der meisten Ursachen, wenn etwas nicht wie erwartet funktioniert.  

Seht diese Dateien als Hilfestellung/Template an, um euch besser orientieren zu können.

### docker-compose.yml (compose.yaml)
```yaml
services:

  web:
    image: nginx:1.29.1-bookworm-perl
    container_name: nginx 
    volumes:
      - /volume1/backup/docker-syno/meshdash-sql/html:/usr/share/nginx/html # place your files for web here
      - /volume1/docker/meshdash-sql/nginx/nginx-conf:/etc/nginx/conf.d # place provided nginx.conf here
      - /volume1/backup/docker-syno/meshdash-sql/nginx/logs:/var/log/nginx
    restart: unless-stopped
    ports:
      - 8080:80    # Port-Mapping external:internal
    expose:
      - 80
    depends_on:
      - php        # Erst starten wenn PHP Up and ready ist
      
  php:
    build: ./php-addon   # <- Ordner, in dem deine Dockerfile liegt
    container_name: php
    volumes:
      - /volume1/backup/docker-syno/meshdash-sql/html:/usr/share/nginx/html # must be same path as above in nginx
    restart: unless-stopped
    working_dir: /usr/share/nginx/html
    ports:
      - "1799:1799/udp"   # UDP für MeshDash
    expose:
      - 9000              # nur intern für nginx (optional)
    
```

#### Hinweis:   
Der Name "Dockerfile" ist Groß-/kleinschrift abhängig.   
Das "D" muss hier großgeschrieben werden.  
Die Datei liegt im Sub-Verzeichnis zur compose.yaml in /php-addon

hier werden die Module Sockets, Zip, Unzip und Netzwerk-Tools installiert,   
welche meshDsh benötigt.  
### Dockerfile
```dockerfile
FROM php:8.4.12-fpm-trixie

# Installiere procps (für pgrep) + Sockets Extension + ggf. andere Basics
# procps für pgrep
RUN apt-get update && apt-get install -y procps net-tools libzip-dev unzip && rm -rf /var/lib/apt/lists/*

# PHP-Sockets installieren
RUN docker-php-ext-install sockets zip

WORKDIR /usr/share/nginx/html

```

### Nginx default.conf
```nginx
server {
    listen 80;
    server_name localhost;
    client_max_body_size 50M;

    root /usr/share/nginx/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

### Features und Bugs
Alle aktuellen Features und gemeldeten Bugs sind im GitHub-Bereich ISSUES  
dokumentiert und können dort verfolgt werden.


  
  
