# Configuration du secret SNYK_TOKEN sur GitHub

Votre token Snyk est : `601268fb-19de-4eb4-9893-fe495d4829e2`

## Étapes pour ajouter le secret sur GitHub

1. **Allez sur votre repository GitHub :**
   - https://github.com/DufortPierre/WR506D

2. **Accédez aux Settings :**
   - Cliquez sur l'onglet **"Settings"** (en haut du repository)

3. **Allez dans Secrets and variables :**
   - Dans le menu de gauche, cliquez sur **"Secrets and variables"**
   - Puis cliquez sur **"Actions"**

4. **Ajoutez un nouveau secret :**
   - Cliquez sur le bouton **"New repository secret"** (en haut à droite)

5. **Remplissez le formulaire :**
   - **Name** : `SNYK_TOKEN`
   - **Secret** : `601268fb-19de-4eb4-9893-fe495d4829e2`
   - Cliquez sur **"Add secret"**

6. **Vérification :**
   - Le secret `SNYK_TOKEN` devrait maintenant apparaître dans la liste
   - ⚠️ **Important** : Vous ne pourrez plus voir la valeur du secret après l'avoir créé (pour des raisons de sécurité)

## Utilisation

Une fois le secret ajouté, le workflow GitHub Actions `.github/workflows/snyk.yml` pourra :
- S'exécuter automatiquement sur chaque push/PR
- Utiliser le token pour authentifier les requêtes Snyk
- Afficher les résultats de l'analyse de sécurité dans les logs du workflow

## Test du workflow

Pour tester que tout fonctionne :
1. Faites un commit et push sur votre branche
2. Allez dans l'onglet **"Actions"** de votre repository GitHub
3. Vous devriez voir le workflow "Snyk Security Scan" s'exécuter
4. Cliquez dessus pour voir les résultats
