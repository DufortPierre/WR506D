#!/bin/bash
# Script de build pour Render
set -e

echo "🔨 Build de l'application Symfony sur Render..."

# Installation des dépendances
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader --no-interaction

# Génération des clés JWT si elles n'existent pas
if [ ! -f "config/jwt/private.pem" ] || [ ! -f "config/jwt/public.pem" ]; then
    echo "🔑 Génération des clés JWT..."
    mkdir -p config/jwt
    
    # Utiliser la passphrase depuis l'environnement ou une valeur par défaut
    PASSPHRASE=${JWT_PASSPHRASE:-default_passphrase_change_me}
    
    # Générer la clé privée
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:"$PASSPHRASE" || {
        echo "⚠️  Erreur lors de la génération de la clé privée, tentative alternative..."
        openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:"$PASSPHRASE" 4096
    }
    
    # Générer la clé publique
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:"$PASSPHRASE" || {
        echo "⚠️  Erreur lors de la génération de la clé publique, tentative alternative..."
        openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem -passin pass:"$PASSPHRASE"
    }
    
    echo "✅ Clés JWT générées"
fi

# Installation des assets
echo "🎨 Installation des assets..."
php bin/console assets:install public --symlink --relative || true

# Installation de l'importmap
echo "📦 Installation de l'importmap..."
php bin/console importmap:install || true

# Cache
echo "🧹 Vidage du cache..."
php bin/console cache:clear --env=prod --no-debug || true

echo "🔥 Réchauffage du cache..."
php bin/console cache:warmup --env=prod || true

# Migrations (si DB disponible)
echo "🗄️  Exécution des migrations..."
php bin/console doctrine:database:create --if-not-exists || true
php bin/console doctrine:migrations:migrate --no-interaction || echo "⚠️  Migrations non exécutées (base de données peut-être non disponible)"

echo "✅ Build terminé !"
