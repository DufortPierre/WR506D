#!/bin/bash

# Script de déploiement pour VPS (FileZilla)
# Usage: ./deploy.sh filezilla
# Ce script doit être exécuté depuis le répertoire du projet

set -e  # Arrêter en cas d'erreur

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Détection du mode de déploiement
DEPLOY_MODE="${1:-filezilla}"

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Déploiement WR506D sur VPS          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Erreur: composer.json non trouvé. Êtes-vous dans le bon répertoire ?${NC}"
    exit 1
fi

# ============================================
# MODE FILEZILLA (déploiement direct)
# ============================================
if [ "$DEPLOY_MODE" = "filezilla" ]; then
    echo -e "${BLUE}📦 Mode de déploiement : FileZilla (sans Docker)${NC}"
    echo ""
    
    # Vérifier si on est sur le serveur (après transfert) ou en local (préparation)
    if [ -d "/var/www/html" ] && [ -w "/var/www/html" ]; then
        # On est sur le serveur, finaliser le déploiement
        echo -e "${YELLOW}🔄 Finalisation du déploiement sur le serveur...${NC}"
        
        cd /var/www/html || exit 1
        
        # Vérifier que .env.prod existe
        if [ ! -f ".env.prod" ] && [ ! -f ".env" ]; then
            echo -e "${RED}❌ Erreur: .env.prod ou .env non trouvé${NC}"
            echo -e "${YELLOW}⚠️  Créez un fichier .env avec la configuration de production${NC}"
            exit 1
        fi
        
        # Utiliser .env.prod si disponible, sinon .env
        if [ -f ".env.prod" ]; then
            export $(cat .env.prod | grep -v '^#' | xargs)
        fi
        
        echo -e "${YELLOW}🔄 Installation des dépendances...${NC}"
        composer install --no-dev --optimize-autoloader --no-interaction || true
        
        echo -e "${YELLOW}🔄 Vidage du cache...${NC}"
        php bin/console cache:clear --env=prod --no-debug || true
        
        echo -e "${YELLOW}🔄 Réchauffage du cache...${NC}"
        php bin/console cache:warmup --env=prod || true
        
        echo -e "${YELLOW}🔄 Vérification de la base de données...${NC}"
        php bin/console doctrine:database:create --if-not-exists 2>/dev/null || true
        
        echo -e "${YELLOW}🔄 Exécution des migrations...${NC}"
        php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || echo -e "${YELLOW}⚠️  Aucune migration à exécuter${NC}"
        
        echo -e "${YELLOW}🔄 Configuration des permissions...${NC}"
        chown -R www-data:www-data var/ public/uploads/ 2>/dev/null || true
        chmod -R 755 var/ public/uploads/ 2>/dev/null || true
        chmod -R 775 var/cache var/log 2>/dev/null || true
        
        echo ""
        echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
        echo -e "${GREEN}║   ✅ Déploiement terminé !            ║${NC}"
        echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
        echo ""
        echo -e "${BLUE}🌐 URLs de l'application :${NC}"
        echo -e "   - API : ${GREEN}https://mmi23e05.mmi-troyes.fr/WR506${NC}"
        echo -e "   - Documentation : ${GREEN}https://mmi23e05.mmi-troyes.fr/WR506/api/docs${NC}"
        echo -e "   - GraphQL : ${GREEN}https://mmi23e05.mmi-troyes.fr/WR506/api/graphql/graphiql${NC}"
        echo ""
        exit 0
    else
        # On est en local, préparer le package pour FileZilla
        echo -e "${YELLOW}🔄 Préparation du package pour FileZilla...${NC}"
        
        # Vérifier que .env.prod existe
        if [ ! -f ".env.prod" ]; then
            echo -e "${YELLOW}⚠️  .env.prod n'existe pas. Création...${NC}"
            if [ -f ".env.prod.example" ]; then
                cp .env.prod.example .env.prod
                echo -e "${YELLOW}⚠️  IMPORTANT: Modifiez .env.prod avec la configuration de production !${NC}"
                echo -e "${YELLOW}⚠️  Notamment : APP_ENV=prod, DATABASE_URL, APP_SECRET, etc.${NC}"
            elif [ -f ".env" ]; then
                cp .env .env.prod
                echo -e "${YELLOW}⚠️  IMPORTANT: Modifiez .env.prod avec la configuration de production !${NC}"
                echo -e "${YELLOW}⚠️  Notamment : APP_ENV=prod, DATABASE_URL, APP_SECRET, etc.${NC}"
            else
                echo -e "${RED}❌ .env ou .env.prod.example non trouvé. Créez .env.prod manuellement.${NC}"
                exit 1
            fi
            echo -e "${YELLOW}⚠️  Appuyez sur Entrée pour continuer ou Ctrl+C pour annuler...${NC}"
            read
        fi
        
        echo -e "${YELLOW}🔄 Mise à jour du code depuis GitHub...${NC}"
        if [ -d ".git" ]; then
            git fetch origin || true
            git pull origin main || echo -e "${YELLOW}⚠️  Aucune mise à jour disponible${NC}"
        else
            echo -e "${YELLOW}⚠️  Ce n'est pas un dépôt git, continuation...${NC}"
        fi
        
        echo -e "${YELLOW}🔄 Installation des dépendances de production...${NC}"
        composer install --no-dev --optimize-autoloader --no-interaction
        
        echo -e "${YELLOW}🔄 Vidage du cache...${NC}"
        APP_ENV=prod php bin/console cache:clear --no-debug || true
        
        echo -e "${YELLOW}🔄 Réchauffage du cache...${NC}"
        APP_ENV=prod php bin/console cache:warmup || true
        
        # Créer un répertoire de déploiement
        DEPLOY_DIR="deploy_package"
        echo -e "${YELLOW}🔄 Création du package de déploiement...${NC}"
        rm -rf "$DEPLOY_DIR"
        mkdir -p "$DEPLOY_DIR"
        
        # Copier les fichiers nécessaires (exclure node_modules, .git, etc.)
        echo -e "${YELLOW}🔄 Copie des fichiers...${NC}"
        rsync -av --progress \
            --exclude='.git' \
            --exclude='.gitignore' \
            --exclude='node_modules' \
            --exclude='.env' \
            --exclude='.env.local' \
            --exclude='.env.*.local' \
            --exclude='var/cache' \
            --exclude='var/log' \
            --exclude='deploy_package' \
            --exclude='.idea' \
            --exclude='.vscode' \
            --exclude='tests' \
            --exclude='phpunit.dist.xml' \
            --exclude='phpcs.xml.dist' \
            --exclude='phpmd.xml' \
            --exclude='phpstan.neon' \
            ./ "$DEPLOY_DIR/"
        
        # Créer les répertoires nécessaires
        mkdir -p "$DEPLOY_DIR/var/cache"
        mkdir -p "$DEPLOY_DIR/var/log"
        mkdir -p "$DEPLOY_DIR/public/uploads"
        
        # Copier .env.prod comme .env sur le serveur
        cp .env.prod "$DEPLOY_DIR/.env"
        
        # Créer un fichier README avec les instructions
        cat > "$DEPLOY_DIR/DEPLOY_INSTRUCTIONS.txt" << 'EOF'
INSTRUCTIONS DE DÉPLOIEMENT VIA FILEZILLA
==========================================

1. CONNEXION FILEZILLA
   - Hôte : mmi23e05.mmi-troyes.fr
   - Protocole : SFTP (SSH File Transfer Protocol)
   - Port : 22 (par défaut)
   - Identifiant : [votre identifiant]
   - Mot de passe : [votre mot de passe]

2. TRANSFERT DES FICHIERS
   - Connectez-vous au serveur
   - Naviguez vers le répertoire : /var/www/html/
   - Transférez TOUS les fichiers de ce package vers /var/www/html/
   - Assurez-vous que le fichier .env est bien transféré

3. CONFIGURATION APACHE
   - Vérifiez que la configuration Apache est en place
   - La configuration doit contenir : Alias /WR506 /var/www/html/public
   - Redémarrez Apache : sudo systemctl restart apache2

4. FINALISATION SUR LE SERVEUR
   - Connectez-vous en SSH au serveur
   - Exécutez : cd /var/www/html && ./deploy.sh filezilla
   - Ou exécutez manuellement :
     * composer install --no-dev --optimize-autoloader
     * php bin/console cache:clear --env=prod
     * php bin/console cache:warmup --env=prod
     * php bin/console doctrine:database:create --if-not-exists
     * php bin/console doctrine:migrations:migrate --no-interaction
     * chown -R www-data:www-data var/ public/uploads/
     * chmod -R 755 var/ public/uploads/
     * chmod -R 775 var/cache var/log

5. VÉRIFICATION
   - Accédez à : https://mmi23e05.mmi-troyes.fr/WR506
   - Vérifiez les logs en cas d'erreur : tail -f var/log/prod.log

IMPORTANT :
- Le fichier .env doit contenir APP_ENV=prod
- Vérifiez que la base de données est accessible
- Les permissions doivent être correctes (www-data:www-data)
EOF
        
        echo ""
        echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
        echo -e "${GREEN}║   ✅ Package prêt pour FileZilla !     ║${NC}"
        echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
        echo ""
        echo -e "${BLUE}📦 Le package se trouve dans : ${GREEN}$DEPLOY_DIR/${NC}"
        echo ""
        echo -e "${BLUE}📋 Instructions :${NC}"
        echo -e "   1. Connectez-vous à FileZilla avec :"
        echo -e "      - Hôte : ${GREEN}mmi23e05.mmi-troyes.fr${NC}"
        echo -e "      - Protocole : ${GREEN}SFTP${NC}"
        echo -e "      - Port : ${GREEN}22${NC}"
        echo ""
        echo -e "   2. Transférez TOUS les fichiers de ${GREEN}$DEPLOY_DIR/${NC}"
        echo -e "      vers ${GREEN}/var/www/html/${NC} sur le serveur"
        echo ""
        echo -e "   3. Connectez-vous en SSH et exécutez :"
        echo -e "      ${GREEN}cd /var/www/html && ./deploy.sh filezilla${NC}"
        echo ""
        echo -e "   4. Accédez à : ${GREEN}https://mmi23e05.mmi-troyes.fr/WR506${NC}"
        echo ""
        echo -e "${YELLOW}📄 Consultez DEPLOY_INSTRUCTIONS.txt dans le package pour plus de détails${NC}"
        echo ""
        exit 0
    fi
else
    echo -e "${RED}❌ Mode non reconnu. Utilisez : ./deploy.sh filezilla${NC}"
    exit 1
fi
