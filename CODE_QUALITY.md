# Guide des outils de qualité de code

Ce projet utilise plusieurs outils pour assurer la qualité du code PHP.

## 📦 Outils installés

1. **PHP_CodeSniffer** (déjà présent)
2. **PHPStan** (analyse statique)
3. **PHPMD** (détection de code problématique)
4. **Snyk** (détection de vulnérabilités)

## 🔧 Configuration

### PHP_CodeSniffer (PSR2)

Le fichier `phpcs.xml.dist` est configuré pour valider le code selon le standard **PSR2**.

**Commandes :**
```bash
# Analyser le code
vendor/bin/phpcs --standard=PSR2 src/

# Corriger automatiquement les erreurs
vendor/bin/phpcbf src/
```

### PHPStan (niveau 5)

Le fichier `phpstan.neon` est configuré avec le niveau d'analyse **5**.

**Commande :**
```bash
vendor/bin/phpstan analyze src/
```

**Niveaux PHPStan :**
- 0-3 : Vérifications de base
- 4-5 : Vérifications moyennes (recommandé)
- 6-9 : Vérifications strictes

### PHPMD

Le fichier `phpmd.xml` est configuré pour détecter les problèmes de code.

**Commande :**
```bash
vendor/bin/phpmd src/ text phpmd.xml
```

**Règles activées :**
- Clean Code
- Code Size
- Controversial (avec exclusions)
- Design
- Naming (avec exclusions)
- Unused Code

## 🚀 Utilisation

### Analyser tout le code

```bash
# PHP_CodeSniffer
vendor/bin/phpcs src/

# PHPStan
vendor/bin/phpstan analyze src/

# PHPMD
vendor/bin/phpmd src/ text phpmd.xml
```

### Corriger automatiquement (PHP_CodeSniffer uniquement)

```bash
vendor/bin/phpcbf src/
```

## 🔄 GitHub Actions

Un workflow GitHub Actions (`.github/workflows/code-quality.yml`) exécute automatiquement :
- PHP_CodeSniffer sur chaque push/PR
- PHPStan sur chaque push/PR
- PHPMD sur chaque push/PR

Les workflows s'exécutent sur :
- Push vers `main` ou `develop`
- Pull Requests vers `main` ou `develop`
- Manuellement via l'onglet Actions

## 📊 Résultats actuels

Après la première analyse :
- **PHP_CodeSniffer** : 91 erreurs et 7 warnings détectés (corrigeables automatiquement avec `phpcbf`)
- **PHPStan** : À exécuter pour voir les résultats
- **PHPMD** : À exécuter pour voir les résultats

## 💡 Recommandations

1. **Corriger d'abord les erreurs PHP_CodeSniffer** avec `phpcbf`
2. **Ensuite, corriger les erreurs PHPStan** (analyse statique)
3. **Enfin, corriger les problèmes PHPMD** (qualité du code)

## 📚 Documentation

- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHPStan](https://phpstan.org/)
- [PHPMD](https://phpmd.org/)
- [PSR-2 Coding Standard](https://www.php-fig.org/psr/psr-2/)
