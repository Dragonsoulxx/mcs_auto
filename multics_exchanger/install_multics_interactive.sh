#!/bin/bash

# Colori per l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funzione per stampare messaggi
print_message() {
    echo -e "${YELLOW}$1${NC}"
}

# Funzione per stampare errori
print_error() {
    echo -e "${RED}ERRORE: $1${NC}" >&2
    exit 1
}

# Funzione per chiedere input all'utente
ask_input() {
    local prompt="$1"
    local default="$2"
    local var_name="$3"
    if [ -n "$default" ]; then
        read -p "$prompt [$default]: " input
        eval "$var_name='${input:-$default}'"
    else
        read -p "$prompt: " input
        eval "$var_name='$input'"
    fi
}

# 1. Controlla se l'utente è root o usa sudo
if [ "$EUID" -ne 0 ]; then
    print_error "Questo script deve essere eseguito come root o con sudo. Usa: sudo ./install_multics_interactive.sh"
fi

# 2. Verifica la versione di Ubuntu
if ! grep -q "Ubuntu 24.04" /etc/os-release; then
    print_message "Attenzione: Questo script è ottimizzato per Ubuntu 24.04. La versione corrente potrebbe non essere compatibile."
    ask_input "Vuoi continuare comunque? (s/n, default: n)" "n" CONTINUE
    if [ "$CONTINUE" != "s" ] && [ "$CONTINUE" != "S" ]; then
        print_error "Installazione annullata."
    fi
fi

print_message "Benvenuto nello script di installazione interattiva di Multics Exchanger su Ubuntu 24.04!"

# 3. Chiedi le informazioni all'utente
ask_input "Inserisci il tuo nome utente sul server (default: ubuntu)" "ubuntu" USERNAME
ask_input "Inserisci l'IP pubblico del server" "" SERVER_IP
ask_input "Inserisci il dominio (lascia vuoto se usi solo l'IP)" "" DOMAIN
ask_input "Inserisci la password per l'utente MySQL (default: secure_password)" "secure_password" MYSQL_PASSWORD

# Chiedi se il backup è un file compresso o una directory
ask_input "Il backup è un file compresso (.zip o .tar.gz) o una directory? (f/d, default: f)" "f" BACKUP_TYPE
if [ "$BACKUP_TYPE" = "f" ] || [ "$BACKUP_TYPE" = "F" ]; then
    ask_input "Inserisci il percorso del file di backup compresso (es. /home/$USERNAME/multics_exchanger.zip)" "/home/$USERNAME/multics_exchanger.zip" BACKUP_FILE
else
    ask_input "Inserisci il percorso della directory di backup (es. /home/$USERNAME/multics_exchanger)" "/home/$USERNAME/multics_exchanger" BACKUP_DIR_SOURCE
fi

ask_input "Inserisci la directory di destinazione per il backup (default: /home/$USERNAME/backup/)" "/home/$USERNAME/backup/" BACKUP_DIR

# 4. Crea la directory di backup se non esiste
sudo mkdir -p "$BACKUP_DIR"
sudo chown -R "$USERNAME:$USERNAME" "$BACKUP_DIR"
sudo chmod -R 755 "$BACKUP_DIR"

# 5. Copia il backup nella directory di destinazione
if [ "$BACKUP_TYPE" = "f" ] || [ "$BACKUP_TYPE" = "F" ]; then
    if [ ! -f "$BACKUP_DIR/$(basename "$BACKUP_FILE")" ]; then
        print_message "Copiando il file di backup in $BACKUP_DIR..."
        sudo cp "$BACKUP_FILE" "$BACKUP_DIR/" || print_error "Impossibile copiare il file di backup. Verifica il percorso."
    else
        print_message "Il file di backup è già presente in $BACKUP_DIR."
    fi
else
    if [ ! -d "$BACKUP_DIR/multics_exchanger" ]; then
        print_message "Copiando la directory di backup in $BACKUP_DIR..."
        if [ -d "$BACKUP_DIR_SOURCE" ]; then
            sudo cp -r "$BACKUP_DIR_SOURCE" "$BACKUP_DIR/multics_exchanger" || print_error "Impossibile copiare la directory di backup. Verifica il percorso."
        else
            print_error "La directory di backup specificata ($BACKUP_DIR_SOURCE) non esiste. Verifica il percorso."
        fi
    else
        print_message "La directory di backup è già presente in $BACKUP_DIR."
    fi
fi

# 6. Aggiorna il sistema
print_message "Aggiornando il sistema..."
sudo apt update && sudo apt upgrade -y || print_error "Errore nell'aggiornamento del sistema."

# 7. Installa i pacchetti necessari per Ubuntu 24.04
print_message "Installando Apache, PHP, MySQL e altri pacchetti..."
sudo apt install apache2 php8.3 libapache2-mod-php8.3 php8.3-mysql mysql-server unzip -y || print_error "Errore nell'installazione dei pacchetti."

# 8. Configura MySQL con un nuovo database e tabelle
print_message "Configurando un nuovo database MySQL e le tabelle..."
sudo systemctl start mysql
sudo systemctl enable mysql

sudo mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS multics_exchanger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'multics_user'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD';
GRANT ALL PRIVILEGES ON multics_exchanger.* TO 'multics_user'@'localhost';
FLUSH PRIVILEGES;

USE multics_exchanger;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    line_data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOF

# 9. Crea la directory della web app
print_message "Creando la directory della web app..."
WEB_DIR="/var/www/multics_exchanger"
sudo mkdir -p "$WEB_DIR"
sudo chown -R www-data:www-data "$WEB_DIR"
sudo chmod -R 755 "$WEB_DIR"

# 10. Decomprimi o copia il backup nella directory della web app
print_message "Installando i file della web app..."
if [ "$BACKUP_TYPE" = "f" ] || [ "$BACKUP_TYPE" = "F" ]; then
    BACKUP_FILENAME=$(basename "$BACKUP_FILE")
    if [[ "$BACKUP_FILENAME" == *.zip ]]; then
        sudo unzip "$BACKUP_DIR/$BACKUP_FILENAME" -d "$WEB_DIR" || print_error "Errore nella decompressione del file .zip."
    elif [[ "$BACKUP_FILENAME" == *.tar.gz ]]; then
        sudo tar -xzf "$BACKUP_DIR/$BACKUP_FILENAME" -C "$WEB_DIR" || print_error "Errore nella decompressione del file .tar.gz."
    else
        print_error "Formato del backup non supportato. Usa .zip o .tar.gz."
    fi
else
    if [ -d "$BACKUP_DIR/multics_exchanger" ]; then
        sudo cp -r "$BACKUP_DIR/multics_exchanger/"* "$WEB_DIR/" || print_error "Errore nella copia della directory di backup."
    else
        print_error "La directory di backup ($BACKUP_DIR/multics_exchanger) non esiste. Verifica il percorso."
    fi
fi

# 11. Configura Apache VirtualHost
print_message "Configurando Apache..."
VHOST_FILE="/etc/apache2/sites-available/multics-exchanger.conf"
if [ -n "$DOMAIN" ]; then
    SERVER_NAME="$DOMAIN"
    SERVER_ALIAS="www.$DOMAIN"
else
    SERVER_NAME="$SERVER_IP"
    SERVER_ALIAS=""
fi

sudo tee "$VHOST_FILE" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName $SERVER_NAME
    ${SERVER_ALIAS:+"ServerAlias $SERVER_ALIAS"}
    DocumentRoot $WEB_DIR

    <Directory $WEB_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/multics-exchanger-error.log
    CustomLog \${APACHE_LOG_DIR}/multics-exchanger-access.log combined
</VirtualHost>
EOF

sudo a2ensite multics-exchanger.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# 12. Aggiorna config.php con il nuovo database e utente
print_message "Aggiornando config.php per il nuovo database e utente..."
CONFIG_FILE="$WEB_DIR/config.php"
if [ -f "$CONFIG_FILE" ]; then
    sudo sed -i "s|define('"'"'DB_USER'"'"', '"'"'root'"'"')|define('"'"'DB_USER'"'"', '"'"'multics_user'"'"')|" "$CONFIG_FILE"
    sudo sed -i "s|define('"'"'DB_PASS'"'"', '"'"'peppe2025'"'"')|define('"'"'DB_PASS'"'"', '"'"'$MYSQL_PASSWORD'"'"')|" "$CONFIG_FILE"
    sudo sed -i "s|define('"'"'SERVER_IP'"'"', '"'"'80.211.129.193'"'"')|define('"'"'SERVER_IP'"'"', '"'"'$SERVER_IP'"'"')|" "$CONFIG_FILE"
    sudo sed -i "s|/var/etc/configmc|$WEB_DIR|" "$CONFIG_FILE"
    print_message "File config.php aggiornato con successo."
else
    print_error "File config.php non trovato in $WEB_DIR."
fi

# 13. Disattiva il firewall UFW
print_message "Disattivando il firewall UFW..."
sudo ufw disable || print_error "Errore nella disattivazione di UFW. Verifica lo stato con 'sudo ufw status'."
print_message "Il firewall UFW è stato disattivato. Tutte le porte sono ora aperte (attenzione alla sicurezza)."

# 14. Offri l'opzione di configurare HTTPS con Let's Encrypt
ask_input "Vuoi configurare HTTPS con Let's Encrypt? (s/n, default: n)" "n" USE_HTTPS
if [ "$USE_HTTPS" = "s" ] || [ "$USE_HTTPS" = "S" ]; then
    if [ -n "$DOMAIN" ]; then
        print_message "Installando Certbot per Let's Encrypt..."
        sudo apt install certbot python3-certbot-apache -y || print_error "Errore nell'installazione di Certbot."

        print_message "Configurando HTTPS per $DOMAIN..."
        sudo certbot --apache -d "$DOMAIN" -d "www.$DOMAIN" || print_error "Errore nella configurazione di HTTPS. Verifica il dominio e riprova."
        print_message "HTTPS configurato con successo. Il sito è ora accessibile via https://$DOMAIN/multics_exchanger/"
    else
        print_error "Devi specificare un dominio per configurare HTTPS con Let's Encrypt."
    fi
fi

# 15. Testa l'accesso pubblico
print_message "Installazione completata! Verifica il sito all'indirizzo:"
if [ -n "$DOMAIN" ]; then
    echo -e "${GREEN}http://$DOMAIN/multics_exchanger/ o https://$DOMAIN/multics_exchanger/ (se HTTPS è configurato)${NC}"
else
    echo -e "${GREEN}http://$SERVER_IP/multics_exchanger/${NC}"
fi

print_message "Controlla i log di Apache per eventuali errori: tail -f /var/log/apache2/multics-exchanger-error.log"