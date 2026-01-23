# 🚀 Déploiement sur Render

Guide simple pour déployer votre application Symfony sur Render (beaucoup plus simple que FileZilla !).

## 📋 Prérequis

- Un compte Render (gratuit) : https://render.com
- Votre code sur GitHub/GitLab/Bitbucket

## 🎯 Déploiement en 3 étapes

### Étape 1 : Préparer le dépôt Git

Assurez-vous que votre code est bien poussé sur GitHub avec les fichiers :
- `render.yaml`
- `.render-build.sh`

```bash
git add render.yaml .render-build.sh
git commit -m "Ajout configuration Render"
git push origin main
```

### Étape 2 : Créer un compte Render

1. Allez sur https://render.com
2. Créez un compte (gratuit avec GitHub)
3. Connectez votre dépôt GitHub

### Étape 3 : Déployer avec Blueprint (le plus simple !)

1. Dans le dashboard Render, cliquez sur **"New +"** → **"Blueprint"**
2. Sélectionnez votre dépôt `WR506D`
3. Render détectera automatiquement le fichier `render.yaml`
4. Cliquez sur **"Apply"**
5. Render créera automatiquement :
   - ✅ Le service web PHP
   - ✅ La base de données PostgreSQL
   - ✅ Toutes les variables d'environnement

**C'est tout !** Render va automatiquement :
- Installer les dépendances
- Générer les clés JWT
- Exécuter les migrations
- Démarrer l'application

## 🔧 Configuration manuelle (alternative)

Si vous préférez configurer manuellement :

### 1. Créer la base de données

1. **"New +"** → **"PostgreSQL"**
2. Configurez :
   - **Name** : `wr506d-db`
   - **Database** : `symfony`
   - **User** : `symfony`
   - **Plan** : Free (ou Starter)
3. Cliquez sur **"Create Database"**

### 2. Déployer l'application web

1. **"New +"** → **"Web Service"**
2. Connectez votre dépôt GitHub
3. Configurez :
   - **Name** : `wr506d-api`
   - **Environment** : `PHP`
   - **Region** : Choisissez le plus proche
   - **Branch** : `main`
   - **Build Command** : `chmod +x .render-build.sh && ./.render-build.sh`
   - **Start Command** : `php -S 0.0.0.0:$PORT -t public public/index.php`
   - **Plan** : Free

4. **Variables d'environnement** :
   - `APP_ENV` = `prod`
   - `APP_SECRET` = (généré automatiquement par Render)
   - `DATABASE_URL` = (copié depuis votre base de données)
   - `JWT_PASSPHRASE` = (généré automatiquement par Render)
   - `CORS_ALLOW_ORIGIN` = `^https?://(.*\.render\.com|localhost|127\.0\.0\.1)(:[0-9]+)?$`
   - `MESSENGER_TRANSPORT_DSN` = `doctrine://default?auto_setup=0`
   - `MAILER_DSN` = `null://null`

5. Cliquez sur **"Create Web Service"**

## 🔑 Génération des clés JWT

Les clés JWT sont générées automatiquement lors du build par le script `.render-build.sh`.

Si vous devez les régénérer manuellement, connectez-vous au Shell Render et exécutez :
```bash
php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

## 🗄️ Exécution des migrations

Les migrations sont exécutées automatiquement lors du build.

Pour les exécuter manuellement :
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

## 🌐 Accéder à votre application

Une fois déployé, Render vous donnera une URL comme :
- **API** : `https://wr506d-api.onrender.com/api`
- **Documentation** : `https://wr506d-api.onrender.com/api/docs`
- **GraphQL** : `https://wr506d-api.onrender.com/api/graphql/graphiql`

## 🔧 Configuration avancée

### Domaine personnalisé

1. Dans votre service web, allez dans **"Settings"** → **"Custom Domains"**
2. Ajoutez votre domaine
3. Configurez les DNS selon les instructions Render

### Variables d'environnement sensibles

Pour les secrets, utilisez les **"Secret Files"** de Render ou les variables d'environnement sécurisées.

### Logs

Les logs sont accessibles dans l'onglet **"Logs"** de votre service.

## ⚠️ Notes importantes

1. **Plan gratuit** : Render met en veille les services gratuits après 15 minutes d'inactivité. Le premier démarrage peut prendre 30-60 secondes.

2. **Base de données** : Le plan gratuit PostgreSQL a des limitations (pas de backup automatique). Pour la production, considérez un plan payant.

3. **Clés JWT** : Les clés JWT sont générées automatiquement lors du premier build et persistées dans le système de fichiers.

4. **Cache** : Le cache Symfony est stocké dans `var/cache`. Sur Render, cela fonctionne bien avec le système de fichiers.

## 🆘 Dépannage

### L'application ne démarre pas
- Vérifiez les logs dans l'onglet "Logs"
- Vérifiez que toutes les variables d'environnement sont définies
- Vérifiez que les clés JWT sont générées

### Erreur de base de données
- Vérifiez que `DATABASE_URL` est correct
- Vérifiez que la base de données est bien créée et accessible
- Vérifiez les migrations : `php bin/console doctrine:migrations:status`

### Erreur 500
- Activez temporairement `APP_DEBUG=1` pour voir les erreurs
- Vérifiez les permissions : `chmod -R 775 var/`
- Vérifiez les logs : `tail -f var/log/prod.log`

## 📚 Ressources

- Documentation Render : https://render.com/docs
- Documentation Symfony sur Render : https://render.com/docs/deploy-php-symfony
