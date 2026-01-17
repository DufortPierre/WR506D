# ⚡ Déploiement Rapide - Guide Express

## 🚀 Déploiement en 5 minutes

### 1. Sur votre VPS, clonez le projet
```bash
cd /var/www
git clone git@github.com:DufortPierre/WR506D.git wr506d
cd wr506d
```

### 2. Configurez les variables d'environnement
```bash
# Créer .env.docker
cp env.docker.example .env.docker
nano .env.docker
# Modifiez les mots de passe !

# Créer .env.prod
cp .env .env.prod
nano .env.prod
# Modifiez APP_ENV=prod et DATABASE_URL
```

### 3. Déployez !
```bash
./deploy.sh
```

### 4. Vérifiez
```bash
curl http://votre-ip/
```

## ✅ C'est tout !

Pour plus de détails, consultez `DEPLOYMENT.md`
