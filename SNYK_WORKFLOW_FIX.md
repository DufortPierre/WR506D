# Correction du workflow Snyk

## Problème identifié

Le workflow Snyk échoue probablement parce que :
1. Le secret `SNYK_TOKEN` n'est pas configuré sur GitHub
2. La configuration du workflow utilisait une action qui n'existe pas

## Solution

### 1. Configurer le secret SNYK_TOKEN (OBLIGATOIRE)

**IMPORTANT** : Pour que le workflow fonctionne, vous DEVEZ configurer le secret `SNYK_TOKEN` sur GitHub.

1. Allez sur : https://github.com/DufortPierre/WR506D/settings/secrets/actions
2. Si le secret `SNYK_TOKEN` n'existe pas :
   - Cliquez sur **"New repository secret"**
   - **Name** : `SNYK_TOKEN`
   - **Secret** : `601268fb-19de-4eb4-9893-fe495d4829e2` (votre token Snyk)
   - Cliquez sur **"Add secret"**

### 2. Workflow corrigé

Le workflow a été corrigé pour :
- Installer Snyk CLI via npm (au lieu d'utiliser une action inexistante)
- Utiliser directement la commande `snyk test`

## Vérification

Après avoir configuré le secret :
1. Le workflow s'exécutera automatiquement sur le prochain push/PR
2. Vous pouvez aussi le déclencher manuellement :
   - Allez dans l'onglet **Actions**
   - Cliquez sur **"Snyk Security Scan"**
   - Cliquez sur **"Run workflow"**

## Voir les logs d'erreur

Pour voir pourquoi un workflow a échoué :
1. Allez dans l'onglet **Actions**
2. Cliquez sur le workflow qui a échoué
3. Cliquez sur le job qui a échoué
4. Développez les étapes pour voir les logs détaillés

Les erreurs les plus courantes :
- `SNYK_TOKEN` manquant → Configurez le secret
- Erreur de dépendances → Vérifiez que composer install fonctionne
- Erreur Snyk → Vérifiez que votre token est valide
