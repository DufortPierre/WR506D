# Configuration Snyk pour le projet Symfony

Ce document explique comment configurer et utiliser Snyk pour analyser les vulnérabilités de sécurité dans ce projet Symfony.

## 📋 Prérequis

- Node.js et npm installés
- Un compte Snyk (gratuit sur [snyk.io](https://snyk.io))

## 🔧 Installation

### 1. Installer Snyk CLI

Snyk CLI a déjà été installé globalement via npm :
```bash
npm install -g snyk
```

### 2. S'authentifier avec Snyk

Pour vous authentifier avec Snyk :

```bash
snyk auth
```

Cette commande ouvrira votre navigateur pour vous connecter à votre compte Snyk. Une fois authentifié, vous pouvez utiliser Snyk CLI.

## 🚀 Utilisation

### Analyser le projet localement

Pour analyser les vulnérabilités dans les dépendances Composer :

```bash
snyk test --file=composer.json
```

### Analyser avec un rapport détaillé

```bash
snyk test --file=composer.json --json > snyk-report.json
```

### Monitorer le projet

Pour surveiller continuellement votre projet :

```bash
snyk monitor --file=composer.json
```

Cette commande envoie un snapshot de vos dépendances à Snyk, qui vous enverra des alertes par email lorsque de nouvelles vulnérabilités sont détectées.

## 🔐 Configuration GitHub Actions

Le workflow GitHub Actions est configuré dans `.github/workflows/snyk.yml`.

### Configuration du secret SNYK_TOKEN

Pour que le workflow fonctionne, vous devez configurer le secret `SNYK_TOKEN` dans GitHub :

1. Allez sur [snyk.io](https://snyk.io) et connectez-vous
2. Accédez à **Settings** > **Account** > **Auth Token**
3. Copiez votre token
4. Sur GitHub, allez dans **Settings** > **Secrets and variables** > **Actions**
5. Cliquez sur **New repository secret**
6. Nom : `SNYK_TOKEN`
7. Valeur : collez votre token Snyk
8. Cliquez sur **Add secret**

### Déclenchement du workflow

Le workflow s'exécute automatiquement :
- À chaque push sur `main` ou `develop`
- À chaque Pull Request vers `main` ou `develop`
- Tous les jours à 2h UTC (analyse planifiée)
- Manuellement via l'onglet Actions (workflow_dispatch)

## 📊 Résultats

Les résultats de l'analyse Snyk sont disponibles :
- Dans l'onglet **Security** de votre repository GitHub
- Dans les logs du workflow GitHub Actions
- Sur votre tableau de bord Snyk (si vous avez utilisé `snyk monitor`)

## 🛠️ Commandes utiles

```bash
# Test simple
snyk test --file=composer.json

# Test avec seuil de sévérité
snyk test --file=composer.json --severity-threshold=high

# Monitorer le projet
snyk monitor --file=composer.json

# Obtenir de l'aide
snyk --help
```

## 📚 Documentation

- [Documentation Snyk CLI](https://docs.snyk.io/snyk-cli)
- [Intégration GitHub Actions](https://docs.snyk.io/integrations/ci-cd-integrations/github-actions-integration)
- [Snyk pour PHP/Composer](https://docs.snyk.io/snyk-cli/scan-applications/snyk-open-source/language-and-package-manager-support/snyk-for-php)
