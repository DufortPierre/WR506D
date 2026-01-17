#!/bin/bash

# Script de déploiement pour VPS
# Usage: ./deploy.sh
# Ce script doit être exécuté depuis le répertoire du projet

set -e  # Arrêter en cas d'erreur

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Déploiement WR506D sur VPS          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Erreur: composer.json non trouvé. Êtes-vous dans le bon répertoire ?${NC}"
    exit 1
fi

# Vérifier que docker-compose.prod.yml existe
if [ ! -f "docker-compose.prod.yml" ]; then
    echo -e "${RED}❌ Erreur: docker-compose.prod.yml non trouvé${NC}"
    exit 1
fi

# Vérifier que .env.docker existe
if [ ! -f ".env.docker" ]; then
    echo -e "${YELLOW}⚠️  .env.docker n'existe pas. Création...${NC}"
    if [ -f "env.docker.example" ]; then
        cp env.docker.example .env.docker
        echo -e "${YELLOW}⚠️  IMPORTANT: Modifiez .env.docker avec des mots de passe sécurisés !${NC}"
        echo -e "${YELLOW}⚠️  Appuyez sur Entrée pour continuer ou Ctrl+C pour annuler...${NC}"
        read
    else
        echo -e "${RED}❌ env.docker.example non trouvé. Créez .env.docker manuellement.${NC}"
        exit 1
    fi
fi

echo -e "${YELLOW}🔄 Mise à jour du code depuis GitHub...${NC}"
if [ -d ".git" ]; then
    git fetch origin || true
    git pull origin main || echo -e "${YELLOW}⚠️  Aucune mise à jour disponible${NC}"
else
    echo -e "${YELLOW}⚠️  Ce n'est pas un dépôt git, continuation...${NC}"
fi

echo -e "${YELLOW}🔄 Arrêt des conteneurs existants...${NC}"
docker-compose -f docker-compose.prod.yml --env-file .env.docker down 2>/dev/null || true

echo -e "${YELLOW}🔄 Construction et démarrage des conteneurs...${NC}"
docker-compose -f docker-compose.prod.yml --env-file .env.docker up -d --build

echo -e "${YELLOW}⏳ Attente du démarrage de la base de données (15 secondes)...${NC}"
sleep 15

# Déterminer le nom du conteneur web
WEB_CONTAINER="wr506d-web"
if ! docker ps | grep -q "$WEB_CONTAINER"; then
    WEB_CONTAINER=$(docker ps --format "{{.Names}}" | grep -E "(web|symfony)" | head -1)
    if [ -z "$WEB_CONTAINER" ]; then
        echo -e "${RED}❌ Aucun conteneur web trouvé${NC}"
        docker ps
        exit 1
    fi
    echo -e "${BLUE}ℹ️  Utilisation du conteneur: $WEB_CONTAINER${NC}"
fi

echo -e "${YELLOW}🔄 Vérification de la base de données...${NC}"
docker exec $WEB_CONTAINER php bin/console doctrine:database:create --if-not-exists 2>/dev/null || true

echo -e "${YELLOW}🔄 Exécution des migrations...${NC}"
docker exec $WEB_CONTAINER php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || echo -e "${YELLOW}⚠️  Aucune migration à exécuter${NC}"

echo -e "${YELLOW}🔄 Installation des dépendances (si nécessaire)...${NC}"
docker exec $WEB_CONTAINER composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true

echo -e "${YELLOW}🔄 Vidage du cache...${NC}"
docker exec $WEB_CONTAINER php bin/console cache:clear --env=prod --no-debug 2>/dev/null || true

echo -e "${YELLOW}🔄 Réchauffage du cache...${NC}"
docker exec $WEB_CONTAINER php bin/console cache:warmup --env=prod 2>/dev/null || true

echo -e "${YELLOW}🔄 Configuration des permissions...${NC}"
docker exec $WEB_CONTAINER chown -R www-data:www-data var/ public/uploads/ 2>/dev/null || true
docker exec $WEB_CONTAINER chmod -R 755 var/ public/uploads/ 2>/dev/null || true

echo -e "${YELLOW}🔄 Vérification de l'état des conteneurs...${NC}"
docker ps --filter "name=wr506d" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ Déploiement terminé !            ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📊 Statut des conteneurs :${NC}"
docker-compose -f docker-compose.prod.yml ps

echo ""
echo -e "${BLUE}🌐 URLs de l'application :${NC}"
echo -e "   - API : ${GREEN}http://mmi23e05.mmi-troyes.fr${NC}"
echo -e "   - Documentation : ${GREEN}http://mmi23e05.mmi-troyes.fr/api/docs${NC}"
echo -e "   - GraphQL : ${GREEN}http://mmi23e05.mmi-troyes.fr/api/graphql/graphiql${NC}"
echo -e "   - phpMyAdmin : ${GREEN}http://mmi23e05.mmi-troyes.fr:8080${NC}"

echo ""
echo -e "${BLUE}🔍 Commandes utiles :${NC}"
echo -e "   - Voir les logs : ${GREEN}docker logs $WEB_CONTAINER -f${NC}"
echo -e "   - Redémarrer : ${GREEN}docker-compose -f docker-compose.prod.yml restart${NC}"
echo -e "   - Arrêter : ${GREEN}docker-compose -f docker-compose.prod.yml down${NC}"
echo ""
