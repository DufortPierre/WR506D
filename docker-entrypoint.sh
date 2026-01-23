#!/bin/bash
set -e

echo "🚀 Démarrage de l'application Symfony..."

# Construction de DATABASE_URL si nécessaire
if [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_PASSWORD" ]; then
    echo "🔧 Construction de DATABASE_URL..."
    export DATABASE_URL="mysql://${MYSQL_USER:-symfony}:${MYSQL_PASSWORD}@${MYSQL_HOST:-wr506d-db}:3306/${MYSQL_DATABASE:-symfony}?serverVersion=8.0&charset=utf8mb4"
fi

# Génération des clés JWT si elles n'existent pas
if [ ! -f "config/jwt/private.pem" ] || [ ! -f "config/jwt/public.pem" ]; then
    echo "🔑 Génération des clés JWT..."
    mkdir -p config/jwt
    
    PASSPHRASE=${JWT_PASSPHRASE:-default_passphrase_change_me}
    
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:"$PASSPHRASE" || {
        openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:"$PASSPHRASE" 4096
    }
    
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:"$PASSPHRASE" || {
        openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem -passin pass:"$PASSPHRASE"
    }
    
    echo "✅ Clés JWT générées"
fi

# Cache et migrations
echo "🧹 Configuration du cache..."
php bin/console cache:clear --env=prod --no-debug || true
php bin/console cache:warmup --env=prod || true

echo "🗄️  Exécution des migrations..."
# Attendre que la base de données soit disponible
echo "⏳ Attente de la base de données..."
for i in {1..30}; do
    if php bin/console doctrine:database:create --if-not-exists 2>/dev/null; then
        echo "✅ Base de données accessible"
        php bin/console doctrine:migrations:migrate --no-interaction && break || echo "⚠️  Migrations non exécutées"
        break
    else
        echo "⏳ Tentative $i/30..."
        sleep 2
    fi
done

# Permissions
chmod -R 775 var/cache var/log || true

echo "✅ Application prête !"

# Si la commande est "start-server", démarrer le serveur PHP
if [ "$1" = "start-server" ]; then
    PORT=${PORT:-10000}
    echo "🌐 Démarrage du serveur PHP sur le port $PORT..."
    exec php -S 0.0.0.0:$PORT -t public public/index.php
fi

# Sinon, exécuter la commande passée en paramètre
exec "$@"
