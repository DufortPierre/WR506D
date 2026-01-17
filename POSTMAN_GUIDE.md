# Guide Postman - Tests CRUD Complets

## 📋 Prérequis

1. **Obtenir un token JWT (avec ROLE_ADMIN pour les opérations d'écriture)**

### 1. Créer un utilisateur admin (si nécessaire)
```bash
php bin/console app:user:create admin@test.com password123 --role=ROLE_ADMIN
```

### 2. Obtenir un token JWT
**POST** `http://localhost:8319/auth`

**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "email": "admin@test.com",
  "password": "password123"
}
```

**Réponse:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### 3. Configurer l'authentification dans Postman
- Onglet **Authorization**
- Type: **Bearer Token**
- Token: Collez le token obtenu

---

## 🎬 ACTORS - Tests CRUD

### ✅ GET - Liste des acteurs
**GET** `http://localhost:8319/api/actors`

**Headers:**
```
Authorization: Bearer {token}
```

### ✅ GET - Détails d'un acteur
**GET** `http://localhost:8319/api/actors/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

### ✅ POST - Créer un acteur (ROLE_ADMIN requis)
**POST** `http://localhost:8319/api/actors`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "lastname": "Doe",
  "firstname": "John",
  "birthDate": "1980-05-15T00:00:00+00:00"
}
```

**Réponse attendue:** 201 Created avec l'acteur créé

### ✅ PATCH - Modifier un acteur (ROLE_ADMIN requis)
**PATCH** `http://localhost:8319/api/actors/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/merge-patch+json
```

**Body (raw JSON):**
```json
{
  "firstname": "Jane"
}
```

**Réponse attendue:** 200 OK avec l'acteur modifié

### ✅ DELETE - Supprimer un acteur (ROLE_ADMIN requis)
**DELETE** `http://localhost:8319/api/actors/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse attendue:** 204 No Content

---

## 🎞️ MOVIES - Tests CRUD

### ✅ GET - Liste des films
**GET** `http://localhost:8319/api/movies`

**Headers:**
```
Authorization: Bearer {token}
```

### ✅ GET - Détails d'un film
**GET** `http://localhost:8319/api/movies/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

### ✅ POST - Créer un film (ROLE_ADMIN requis)
**POST** `http://localhost:8319/api/movies`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "name": "The Matrix",
  "description": "A science fiction action film",
  "duration": 136,
  "releaseDate": "1999-03-31T00:00:00+00:00",
  "online": true,
  "nbEntries": 1000000,
  "director": "/api/directors/1"
}
```

**Note:** Remplacez `/api/directors/1` par l'ID d'un réalisateur existant

**Réponse attendue:** 201 Created avec le film créé

### ✅ PATCH - Modifier un film (ROLE_ADMIN requis)
**PATCH** `http://localhost:8319/api/movies/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/merge-patch+json
```

**Body (raw JSON):**
```json
{
  "duration": 150,
  "online": false
}
```

**Réponse attendue:** 200 OK avec le film modifié

### ✅ DELETE - Supprimer un film (ROLE_ADMIN requis)
**DELETE** `http://localhost:8319/api/movies/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse attendue:** 204 No Content

---

## 📂 CATEGORIES - Tests CRUD

### ✅ GET - Liste des catégories
**GET** `http://localhost:8319/api/categories`

**Headers:**
```
Authorization: Bearer {token}
```

### ✅ GET - Détails d'une catégorie
**GET** `http://localhost:8319/api/categories/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

### ✅ POST - Créer une catégorie (ROLE_ADMIN requis)
**POST** `http://localhost:8319/api/categories`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "name": "Science Fiction",
  "namecategory": "Sci-Fi",
  "relationMovies": "many-to-many"
}
```

**Réponse attendue:** 201 Created avec la catégorie créée

### ✅ PATCH - Modifier une catégorie (ROLE_ADMIN requis)
**PATCH** `http://localhost:8319/api/categories/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/merge-patch+json
```

**Body (raw JSON):**
```json
{
  "name": "Sci-Fi Updated"
}
```

**Réponse attendue:** 200 OK avec la catégorie modifiée

### ✅ DELETE - Supprimer une catégorie (ROLE_ADMIN requis)
**DELETE** `http://localhost:8319/api/categories/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse attendue:** 204 No Content

---

## ✅ Vérifications à faire

1. **Création (POST)** : Vérifier que l'entité est créée avec un ID généré
2. **Lecture (GET)** : Vérifier que les données sont correctement retournées
3. **Modification (PATCH)** : Vérifier que seule la propriété modifiée change
4. **Suppression (DELETE)** : Vérifier que l'entité n'existe plus après suppression
5. **Permissions** : Tester avec un utilisateur ROLE_USER (doit échouer pour POST/PATCH/DELETE)
6. **Validation** : Tester avec des données invalides (doit retourner 422 Unprocessable Entity)

---

## 🔍 Codes de réponse attendus

- **200 OK** : GET, PATCH réussi
- **201 Created** : POST réussi
- **204 No Content** : DELETE réussi
- **401 Unauthorized** : Token manquant ou invalide
- **403 Forbidden** : Permissions insuffisantes (ROLE_USER tentant POST/PATCH/DELETE)
- **404 Not Found** : Ressource non trouvée
- **422 Unprocessable Entity** : Données invalides (validation échouée)
