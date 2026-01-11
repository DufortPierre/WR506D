# Guide : Trouver votre token Snyk

Il existe deux types de tokens Snyk selon votre usage :

## 1. Token d'authentification personnelle (pour CLI)

Ce token est utilisé pour l'authentification CLI personnelle.

### Comment le trouver :

1. Allez sur [snyk.io](https://snyk.io)
2. Connectez-vous à votre compte
3. **En haut à droite**, cliquez sur votre **avatar/profile**
4. Dans le menu déroulant, cliquez sur **"Account Settings"** ou **"Settings"**
5. Dans le menu de gauche, cherchez **"Auth Token"** ou **"API Token"**
6. Cliquez dessus
7. Vous verrez votre token (commence souvent par `snyk_token_...`)
8. Cliquez sur **"Show"** ou **"Copy"** pour le copier

**Chemin alternatif :**
- Après connexion, allez directement sur : `https://app.snyk.io/account`
- Ou : `https://app.snyk.io/manage/account`

## 2. Service Account Token (pour GitHub Actions / CI/CD)

Ce token est recommandé pour les intégrations CI/CD comme GitHub Actions.

### Comment le créer :

1. Allez sur [snyk.io](https://snyk.io)
2. Connectez-vous
3. Allez dans votre **organisation** (dans votre cas : `dufortpierre`)
4. Cliquez sur **Settings** (en haut à droite ou dans le menu)
5. Dans le menu de gauche, cliquez sur **"General"**
6. Cherchez la section **"Organization API key"**
7. Cliquez sur le bouton **"Manage service accounts"**
8. Cliquez sur **"Add service account"** ou **"Create service account"**
9. Donnez un nom (ex: "GitHub Actions WR506D")
10. Sélectionnez les permissions appropriées (au minimum "Viewer" pour les tests)
11. Cliquez sur **"Create"**
12. **IMPORTANT** : Copiez le token immédiatement car il ne sera affiché qu'une seule fois !

## Recommandation

Pour GitHub Actions, utilisez un **Service Account Token** (option 2) car :
- Il est lié à l'organisation
- Il peut avoir des permissions spécifiques
- Il ne dépend pas de votre compte personnel
- C'est plus sécurisé pour CI/CD

## Vérification

Une fois le token copié, vous pouvez le tester avec :

```bash
snyk auth <votre_token>
```

Ou pour tester depuis GitHub Actions, ajoutez-le comme secret `SNYK_TOKEN`.
