# WR506D - API Symfony

API REST développée avec Symfony 7 et API Platform.

## 🚀 Démarrage rapide

### Authentification

L'API utilise l'authentification JWT. Pour accéder aux endpoints protégés, vous devez d'abord obtenir un token.

#### 1. Obtenir un token JWT

**Endpoint:** `POST /auth`

**Corps de la requête (JSON):**
```json
{
  "email": "votre_email@example.com",
  "password": "votre_mot_de_passe"
}
```

**Réponse (200 OK):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### 2. Utiliser le token

Ajoutez le token dans l'en-tête `Authorization` de vos requêtes :

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Exemple avec curl:**
```bash
curl -X GET http://localhost:8319/api/actors \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

**Exemple avec Postman:**
1. Sélectionnez "Authorization" dans l'onglet
2. Type: "Bearer Token"
3. Token: collez votre token JWT

## 📚 Endpoints disponibles

### Endpoints publics
- `GET /` - Informations sur l'API
- `GET /api/docs` - Documentation API (Swagger)

### Endpoints authentifiés (ROLE_USER)
- `GET /api/actors` - Liste des acteurs
- `GET /api/actors/{id}` - Détails d'un acteur
- `GET /api/movies` - Liste des films
- `GET /api/movies/{id}` - Détails d'un film
- `GET /api/categories` - Liste des catégories
- `GET /api/me` - Informations sur l'utilisateur connecté

### Endpoints admin (ROLE_ADMIN)
- `POST /api/actors` - Créer un acteur
- `PATCH /api/actors/{id}` - Modifier un acteur
- `DELETE /api/actors/{id}` - Supprimer un acteur
- `POST /api/movies` - Créer un film
- `PATCH /api/movies/{id}` - Modifier un film
- `DELETE /api/movies/{id}` - Supprimer un film
- `POST /api/api-keys` - Créer une clé API
- `GET /api/api-keys` - Liste des clés API

### Authentification à deux facteurs (2FA)
- `POST /api/2fa/enable` - Activer la 2FA
- `POST /api/2fa/verify-enable` - Vérifier l'activation
- `POST /api/2fa/verify` - Vérifier un code 2FA
- `POST /api/2fa/disable` - Désactiver la 2FA
- `GET /api/2fa/status` - Statut de la 2FA

### Démonstration Serializer
- `GET /api/serializer-demo/movies` - Démonstration de sérialisation
- `GET /api/serializer-demo/actors` - Démonstration de sérialisation

## 🔑 Méthodes d'authentification

L'API supporte deux méthodes d'authentification :

### 1. JWT Token (recommandé)
Obtenez un token via `POST /auth` et utilisez-le dans l'en-tête `Authorization: Bearer <token>`

### 2. API Key
Utilisez une clé API dans l'en-tête `X-API-Key: <votre_clé_api>`

**Note:** Les clés API doivent être générées par un administrateur via `POST /api/api-keys`

## 🛠️ Commandes utiles

### Générer une clé API
```bash
php bin/console app:api-key:generate <user_id>
```

### Afficher les statistiques
```bash
php bin/console app:stats <type>
# Types: movies, actors, categories, images, all
```

### Charger les fixtures
```bash
php bin/console doctrine:fixtures:load
```

## 📖 Documentation

- Documentation API: `http://localhost:8319/api/docs`
- GraphQL Playground: `http://localhost:8319/api/graphql/graphiql`

## 🔒 Sécurité

- Toutes les routes `/api/*` nécessitent une authentification (JWT ou API Key)
- Les opérations de lecture nécessitent `ROLE_USER`
- Les opérations d'écriture nécessitent `ROLE_ADMIN`
- Rate limiting activé pour l'API (100 req/h pour anonymes, 1000 req/h pour authentifiés)

## 🧪 Tests

```bash
php bin/phpunit
```

## 📝 Notes

- Les tokens JWT expirent après 1 heure (3600 secondes)
- Les rate limits sont appliqués par IP (anonymes) ou par utilisateur (authentifiés)
- La documentation Swagger est accessible sans authentification
