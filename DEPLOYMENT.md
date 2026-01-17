# 🚀 Guide de Déploiement sur VPS

## 📋 Prérequis

- VPS avec Docker et Docker Compose installés
- Domaine configuré (optionnel mais recommandé)
- Accès SSH au VPS
- Ports ouverts : 80, 443 (et 8080 pour phpMyAdmin si nécessaire)

---

## 🔧 Étape 1 : Préparation du VPS

### 1.1 Connexion SSH
```bash
ssh user@votre-vps-ip
```

### 1.2 Installation de Docker (si pas déjà installé)
```bash
# Mise à jour
sudo apt update && sudo apt upgrade -y

# Installation Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Installation Docker Compose
sudo apt install docker-compose -y

# Vérification
docker --version
docker-compose --version
```

### 1.3 Création du répertoire de travail
```bash
mkdir -p /var/www/wr506d
cd /var/www/wr506d
```

---

## 📥 Étape 2 : Cloner le Projet

### 2.1 Cloner depuis GitHub
```bash
git clone git@github.com:DufortPierre/WR506D.git .
# Ou avec HTTPS
git clone https://github.com/DufortPierre/WR506D.git .
```

### 2.2 Vérifier la branche
```bash
git checkout main
git pull origin main
```

---

## 🔐 Étape 3 : Configuration de l'Environnement

### 3.1 Créer le fichier .env.prod
```bash
cp .env .env.prod
nano .env.prod
```

### 3.2 Configuration minimale requise
```env
# Environnement
APP_ENV=prod
APP_SECRET=GENERATE_A_RANDOM_SECRET_HERE

# Base de données (à adapter selon votre configuration)
DATABASE_URL="mysql://symfony:PASSWORD@db:3306/symfony?serverVersion=10.5&charset=utf8mb4"

# CORS (remplacer par votre domaine)
CORS_ALLOW_ORIGIN=https://votre-domaine.com

# Mailer (optionnel)
MAILER_DSN=null://null
```

### 3.3 Générer APP_SECRET
```bash
php bin/console secrets:generate-keys
php bin/console secrets:set APP_SECRET
# Entrez une clé secrète aléatoire
```

---

## 🐳 Étape 4 : Configuration Docker pour Production

### 4.1 Créer docker-compose.prod.yml
```yaml
version: '3.8'

services:
  web:
    image: mmi3docker/symfony-2024
    container_name: wr506d-web
    restart: always
    volumes:
      - ./:/var/www/html
      - ./apache2/sites-enabled/:/etc/apache2/sites-enabled/
    ports:
      - "80:80"
      - "443:443"
    environment:
      - APP_ENV=prod
    depends_on:
      - db
    networks:
      - wr506d-network

  db:
    image: mariadb:10.5
    container_name: wr506d-db
    restart: always
    volumes:
      - db-data:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-CHANGE_ME}
      MYSQL_USER: symfony
      MYSQL_PASSWORD: ${DB_PASSWORD:-CHANGE_ME}
      MYSQL_DATABASE: symfony
    networks:
      - wr506d-network

  phpmyadmin:
    image: phpmyadmin
    container_name: wr506d-admin
    restart: always
    ports:
      - "8080:80"
    environment:
      PMA_HOST: db
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-CHANGE_ME}
    networks:
      - wr506d-network
    profiles:
      - tools

volumes:
  db-data:

networks:
  wr506d-network:
    driver: bridge
```

### 4.2 Créer .env.docker
```env
DB_ROOT_PASSWORD=VOTRE_MOT_DE_PASSE_ROOT
DB_PASSWORD=VOTRE_MOT_DE_PASSE_SYMFONY
```

---

## 🔒 Étape 5 : Configuration Apache pour Production

### 5.1 Mettre à jour la configuration Apache
```bash
nano apache2/sites-enabled/000-default.conf
```

```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    ServerAlias www.votre-domaine.com
    
    DocumentRoot /var/www/html/public
    DirectoryIndex index.php

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    # Security Headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"

    <IfModule mod_rewrite.c>
        RewriteEngine On
        
        # Redirect HTTP to HTTPS (si SSL configuré)
        # RewriteCond %{HTTPS} off
        # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

        RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
        RewriteRule .* - [E=BASE:%1]

        RewriteCond %{HTTP:Authorization} .+
        RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]

        RewriteCond %{ENV:REDIRECT_STATUS} =""
        RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]

        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ %{ENV:BASE}/index.php [L]
    </IfModule>
</VirtualHost>
```

---

## 🚀 Étape 6 : Déploiement

### 6.1 Arrêter les conteneurs existants (si nécessaire)
```bash
docker-compose down
```

### 6.2 Construire et démarrer les conteneurs
```bash
docker-compose -f docker-compose.prod.yml --env-file .env.docker up -d --build
```

### 6.3 Vérifier que les conteneurs sont démarrés
```bash
docker ps
```

---

## 🗄️ Étape 7 : Configuration de la Base de Données

### 7.1 Attendre que la base de données soit prête
```bash
docker exec wr506d-web php bin/console doctrine:database:create --if-not-exists
```

### 7.2 Exécuter les migrations
```bash
docker exec wr506d-web php bin/console doctrine:migrations:migrate --no-interaction
```

### 7.3 Charger les fixtures (optionnel)
```bash
docker exec wr506d-web php bin/console doctrine:fixtures:load --no-interaction
```

---

## ⚙️ Étape 8 : Optimisation Production

### 8.1 Vider et réchauffer le cache
```bash
docker exec wr506d-web php bin/console cache:clear --env=prod --no-debug
docker exec wr506d-web php bin/console cache:warmup --env=prod
```

### 8.2 Optimiser les autoloaders
```bash
docker exec wr506d-web composer dump-autoload --optimize --classmap-authoritative --no-dev
```

### 8.3 Vérifier les permissions
```bash
docker exec wr506d-web chown -R www-data:www-data var/
docker exec wr506d-web chmod -R 755 var/
docker exec wr506d-web chmod -R 755 public/uploads/
```

---

## 🔒 Étape 9 : Configuration SSL/HTTPS (Recommandé)

### 9.1 Installation de Certbot
```bash
sudo apt install certbot python3-certbot-apache -y
```

### 9.2 Génération du certificat SSL
```bash
sudo certbot --apache -d votre-domaine.com -d www.votre-domaine.com
```

### 9.3 Renouvellement automatique
```bash
sudo certbot renew --dry-run
```

---

## 📊 Étape 10 : Vérification

### 10.1 Vérifier les logs
```bash
# Logs de l'application
docker logs wr506d-web

# Logs de la base de données
docker logs wr506d-db
```

### 10.2 Tester l'API
```bash
# Test de santé
curl http://votre-domaine.com/

# Test d'authentification
curl -X POST http://votre-domaine.com/auth \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password123"}'
```

---

## 🔄 Étape 11 : Mise à Jour (CI/CD)

### 11.1 Script de déploiement automatique
Créez `deploy.sh` :
```bash
#!/bin/bash
set -e

echo "🔄 Mise à jour du code..."
git pull origin main

echo "🔄 Reconstruction des conteneurs..."
docker-compose -f docker-compose.prod.yml --env-file .env.docker up -d --build

echo "🔄 Exécution des migrations..."
docker exec wr506d-web php bin/console doctrine:migrations:migrate --no-interaction

echo "🔄 Vider le cache..."
docker exec wr506d-web php bin/console cache:clear --env=prod --no-debug
docker exec wr506d-web php bin/console cache:warmup --env=prod

echo "✅ Déploiement terminé!"
```

### 11.2 Rendre le script exécutable
```bash
chmod +x deploy.sh
```

---

## 🛡️ Sécurité Production

### Checklist de sécurité :

- [ ] Changer tous les mots de passe par défaut
- [ ] Configurer un firewall (UFW)
- [ ] Désactiver phpMyAdmin en production (ou le protéger)
- [ ] Configurer les backups automatiques
- [ ] Activer les logs de sécurité
- [ ] Configurer les rate limits
- [ ] Utiliser HTTPS uniquement
- [ ] Restreindre l'accès SSH (clés uniquement)
- [ ] Configurer fail2ban
- [ ] Mettre à jour régulièrement les dépendances

### Configuration Firewall
```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

---

## 📝 Variables d'Environnement Importantes

| Variable | Description | Exemple |
|----------|-------------|---------|
| `APP_ENV` | Environnement | `prod` |
| `APP_SECRET` | Clé secrète | Généré aléatoirement |
| `DATABASE_URL` | URL de connexion DB | `mysql://user:pass@host:3306/db` |
| `CORS_ALLOW_ORIGIN` | Origines CORS autorisées | `https://votre-domaine.com` |

---

## 🆘 Dépannage

### Problème : Conteneur ne démarre pas
```bash
docker logs wr506d-web
docker-compose -f docker-compose.prod.yml logs
```

### Problème : Erreur de connexion à la base de données
```bash
# Vérifier que la DB est accessible
docker exec wr506d-web php bin/console doctrine:database:create --if-not-exists
```

### Problème : Permissions refusées
```bash
docker exec wr506d-web chown -R www-data:www-data var/ public/uploads/
```

### Problème : Cache corrompu
```bash
docker exec wr506d-web rm -rf var/cache/*
docker exec wr506d-web php bin/console cache:warmup --env=prod
```

---

## 📚 Ressources

- Documentation Symfony : https://symfony.com/doc/current/deployment.html
- Documentation Docker : https://docs.docker.com/
- Let's Encrypt : https://letsencrypt.org/

---

## ✅ Checklist de Déploiement

- [ ] VPS configuré avec Docker
- [ ] Code cloné depuis GitHub
- [ ] Variables d'environnement configurées
- [ ] Base de données créée et migrations exécutées
- [ ] Cache vidé et réchauffé
- [ ] Permissions configurées
- [ ] SSL/HTTPS configuré (optionnel mais recommandé)
- [ ] Firewall configuré
- [ ] Backups configurés
- [ ] Monitoring configuré (optionnel)
- [ ] Tests de l'API effectués

---

**🎉 Votre application est maintenant déployée sur votre VPS !**
