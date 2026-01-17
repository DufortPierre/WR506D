#!/bin/bash

# Script de configuration initiale pour VPS
# À exécuter UNE SEULE FOIS sur le VPS pour la première installation
# Usage: ./SETUP_VPS.sh

set -e

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Configuration Initiale VPS          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# Variables
DEPLOY_DIR="/var/www/wr506d"
GIT_REPO="https://github.com/DufortPierre/WR506D.git"

# Vérifier si on est root ou sudo
if [ "$EUID" -ne 0 ]; then 
    SUDO="sudo"
else
    SUDO=""
fi

echo -e "${YELLOW}📋 Étape 1/6 : Vérification des prérequis...${NC}"

# Vérifier Docker
if ! command -v docker &> /dev/null; then
    echo -e "${YELLOW}📦 Installation de Docker...${NC}"
    curl -fsSL https://get.docker.com -o get-docker.sh
    $SUDO sh get-docker.sh
    $SUDO usermod -aG docker $USER
    rm get-docker.sh
    echo -e "${GREEN}✅ Docker installé${NC}"
    echo -e "${YELLOW}⚠️  Vous devez vous déconnecter et reconnecter pour que les groupes prennent effet${NC}"
else
    echo -e "${GREEN}✅ Docker est installé${NC}"
fi

# Vérifier Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo -e "${YELLOW}📦 Installation de Docker Compose...${NC}"
    $SUDO apt update
    $SUDO apt install -y docker-compose
    echo -e "${GREEN}✅ Docker Compose installé${NC}"
else
    echo -e "${GREEN}✅ Docker Compose est installé${NC}"
fi

# Vérifier Git
if ! command -v git &> /dev/null; then
    echo -e "${YELLOW}📦 Installation de Git...${NC}"
    $SUDO apt update
    $SUDO apt install -y git
    echo -e "${GREEN}✅ Git installé${NC}"
else
    echo -e "${GREEN}✅ Git est installé${NC}"
fi

echo ""
echo -e "${YELLOW}📋 Étape 2/6 : Création du répertoire...${NC}"
$SUDO mkdir -p $DEPLOY_DIR
$SUDO chown -R $USER:$USER $DEPLOY_DIR
echo -e "${GREEN}✅ Répertoire créé : $DEPLOY_DIR${NC}"

echo ""
echo -e "${YELLOW}📋 Étape 3/6 : Clonage du projet...${NC}"
cd $DEPLOY_DIR
if [ -d ".git" ]; then
    echo -e "${YELLOW}⚠️  Le projet existe déjà. Mise à jour...${NC}"
    git pull origin main
else
    git clone $GIT_REPO .
    echo -e "${GREEN}✅ Projet cloné${NC}"
fi

echo ""
echo -e "${YELLOW}📋 Étape 4/6 : Configuration des variables d'environnement...${NC}"

# Générer les mots de passe
DB_ROOT_PASS=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
DB_PASS=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
APP_SECRET=$(openssl rand -hex 32)

# Créer .env.docker
cat > .env.docker << EOF
# Configuration Docker - Généré le $(date)
DB_ROOT_PASSWORD=$DB_ROOT_PASS
DB_PASSWORD=$DB_PASS
EOF

# Créer .env.prod
cat > .env.prod << EOF
# Environnement de production
APP_ENV=prod
APP_SECRET=$APP_SECRET
DATABASE_URL="mysql://symfony:$DB_PASS@db:3306/symfony?serverVersion=10.5&charset=utf8mb4"
CORS_ALLOW_ORIGIN=https://mmi23e05.mmi-troyes.fr
MAILER_DSN=null://null
EOF

echo -e "${GREEN}✅ Fichiers de configuration créés${NC}"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT - Notez ces informations :${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "DB_ROOT_PASSWORD: ${GREEN}$DB_ROOT_PASS${NC}"
echo -e "DB_PASSWORD: ${GREEN}$DB_PASS${NC}"
echo -e "APP_SECRET: ${GREEN}$APP_SECRET${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

echo -e "${YELLOW}📋 Étape 5/6 : Rendre les scripts exécutables...${NC}"
chmod +x deploy.sh
echo -e "${GREEN}✅ Scripts prêts${NC}"

echo ""
echo -e "${YELLOW}📋 Étape 6/6 : Déploiement de l'application...${NC}"
echo -e "${BLUE}Lancement du script de déploiement...${NC}"
./deploy.sh

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ Configuration terminée !          ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📝 Prochaines étapes :${NC}"
echo -e "   1. Si Docker vient d'être installé, déconnectez-vous et reconnectez-vous"
echo -e "   2. Testez l'application : ${GREEN}http://mmi23e05.mmi-troyes.fr/WR506${NC}"
echo -e "   3. Consultez les logs si nécessaire : docker logs wr506d-web"
echo ""
echo -e "${BLUE}🌐 URLs de l'application :${NC}"
echo -e "   - API : ${GREEN}http://mmi23e05.mmi-troyes.fr/WR506${NC}"
echo -e "   - Documentation : ${GREEN}http://mmi23e05.mmi-troyes.fr/WR506/api/docs${NC}"
echo -e "   - GraphQL : ${GREEN}http://mmi23e05.mmi-troyes.fr/WR506/api/graphql/graphiql${NC}"
echo ""
