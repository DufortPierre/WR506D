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
php bin/console doctrine:database:create --if-not-exists || true
php bin/console doctrine:migrations:migrate --no-interaction || echo "⚠️  Migrations non exécutées"

# Permissions
chmod -R 775 var/cache var/log || true

echo "✅ Application prête !"

# Exécuter la commande passée en paramètre
exec "$@"
