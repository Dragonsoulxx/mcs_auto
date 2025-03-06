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

# 1. Controlla se l'utente è root o usa sudo
if [ "$EUID" -ne 0 ]; then
    print_error "Questo script deve essere eseguito come root o con sudo. Usa: sudo ./fix_encoding.sh"
fi

# 2. Verifica se XAMPP è installato
if [ ! -d "/opt/lampp" ]; then
    print_error "XAMPP non sembra essere installato in /opt/lampp. Verifica l'installazione."
fi

# 3. Directory della web app
WEB_DIR="/opt/lampp/htdocs/multics_exchanger"
if [ ! -d "$WEB_DIR" ]; then
    print_error "La directory $WEB_DIR non esiste. Verifica il percorso della tua web app."
fi

print_message "Inizio della correzione dell'encoding e configurazione UTF-8..."

# 4. Correggi l'encoding dei file PHP e CSS in UTF-8 senza BOM
print_message "Convertendo i file in UTF-8 senza BOM..."
for file in "$WEB_DIR"/*.php "$WEB_DIR/css"/*.css; do
    if [ -f "$file" ]; then
        # Usa iconv per convertire in UTF-8 senza BOM
        iconv -f UTF-8 -t UTF-8 -c "$file" -o "$file.tmp" 2>/dev/null || cp "$file" "$file.tmp"
        # Sostituisci il file originale con la versione senza BOM
        mv "$file.tmp" "$file"
        print_message "Convertito: $file"
    fi
done

# 5. Aggiungi l'header UTF-8 nei file PHP
print_message "Aggiungendo header UTF-8 nei file PHP..."
for file in "$WEB_DIR"/*.php; do
    if [ -f "$file" ]; then
        # Controlla se l'header esiste già
        if ! grep -q "header('Content-Type: text/html; charset=UTF-8');" "$file"; then
            # Aggiungi l'header all'inizio del file, dopo <?php
            sed -i '2i header('"'"'Content-Type: text/html; charset=UTF-8'"'"');' "$file"
            print_message "Aggiunto header UTF-8 in: $file"
        else
            print_message "Header UTF-8 già presente in: $file"
        fi
    fi
done

# 6. Verifica e configura il database MySQL per UTF-8
print_message "Configurando il database MySQL per UTF-8..."
MYSQL_CMD="/opt/lampp/bin/mysql -u root -ppeppe2025 multics_exchanger"

# Esegui comandi SQL per impostare UTF-8
SQL_COMMANDS="
ALTER DATABASE multics_exchanger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE user_lines CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
"

echo "$SQL_COMMANDS" | $MYSQL_CMD 2>/dev/null || print_error "Errore nella configurazione del database MySQL. Verifica le credenziali in config.php o il database multics_exchanger."

# 7. Aggiorna la configurazione di Apache per UTF-8
print_message "Aggiornando la configurazione di Apache..."
APACHE_CONF="/opt/lampp/apache2/conf/httpd.conf"

if [ -f "$APACHE_CONF" ]; then
    if ! grep -q "AddDefaultCharset UTF-8" "$APACHE_CONF"; then
        echo "AddDefaultCharset UTF-8" >> "$APACHE_CONF"
        print_message "Aggiunto AddDefaultCharset UTF-8 in $APACHE_CONF"
    else
        print_message "AddDefaultCharset UTF-8 già presente in $APACHE_CONF"
    fi
else
    print_error "File di configurazione Apache $APACHE_CONF non trovato."
fi

# 8. Riavvia Apache per applicare le modifiche
print_message "Riavviando Apache..."
/opt/lampp/lampp restart || print_error "Errore nel riavvio di Apache. Verifica i log in /opt/lampp/logs/error_log."

# 9. Verifica l'encoding dei file convertiti
print_message "Verificando l'encoding dei file..."
for file in "$WEB_DIR"/*.php "$WEB_DIR/css"/*.css; do
    if [ -f "$file" ]; then
        ENCODING=$(file -i "$file" | grep -o "charset=utf-8")
        if [ -n "$ENCODING" ]; then
            print_message "File $file è in UTF-8: $ENCODING"
        else
            print_error "File $file non è in UTF-8. Controlla manualmente."
        fi
    fi
done

print_message "Correzione completata con successo! Verifica il sito per assicurarti che i caratteri speciali siano visualizzati correttamente."