# 🚀 Déploiement sur VPS - Guide Simple

## Connexion
```bash
ssh mmi23e05@mmi23e05.mmi-troyes.fr
```

## Installation en 3 étapes

### Option 1 : Installation Automatique (Recommandé)

```bash
# Télécharger et exécuter le script d'installation
cd /var/www
sudo mkdir -p wr506d
sudo chown -R $USER:$USER wr506d
cd wr506d
curl -o SETUP_VPS.sh https://raw.githubusercontent.com/DufortPierre/WR506D/main/SETUP_VPS.sh
chmod +x SETUP_VPS.sh
./SETUP_VPS.sh
```

### Option 2 : Installation Manuelle

```bash
# 1. Aller dans le répertoire
cd /var/www
sudo mkdir -p wr506d
sudo chown -R $USER:$USER wr506d
cd wr506d

# 2. Cloner le projet
git clone https://github.com/DufortPierre/WR506D.git .

# 3. Créer .env.docker (générer des mots de passe)
DB_ROOT_PASS=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
DB_PASS=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
echo "DB_ROOT_PASSWORD=$DB_ROOT_PASS" > .env.docker
echo "DB_PASSWORD=$DB_PASS" >> .env.docker

# 4. Créer .env.prod
APP_SECRET=$(openssl rand -hex 32)
cat > .env.prod << EOF
APP_ENV=prod
APP_SECRET=$APP_SECRET
DATABASE_URL="mysql://symfony:$DB_PASS@db:3306/symfony?serverVersion=10.5&charset=utf8mb4"
CORS_ALLOW_ORIGIN=https://mmi23e05.mmi-troyes.fr
MAILER_DSN=null://null
EOF

# 5. Déployer
chmod +x deploy.sh
./deploy.sh
```

## Vérification

```bash
# Vérifier les conteneurs
docker ps

# Tester l'API
curl http://mmi23e05.mmi-troyes.fr/
curl http://mmi23e05.mmi-troyes.fr/api/docs
```

## Mise à jour

```bash
cd /var/www/wr506d
git pull origin main
./deploy.sh
```

## URLs

- **API** : http://mmi23e05.mmi-troyes.fr
- **Documentation** : http://mmi23e05.mmi-troyes.fr/api/docs
- **GraphQL** : http://mmi23e05.mmi-troyes.fr/api/graphql/graphiql
- **phpMyAdmin** : http://mmi23e05.mmi-troyes.fr:8080
