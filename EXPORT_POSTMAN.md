# 📤 Comment Exporter une Collection Postman en JSON

## Méthode 1 : Via l'Interface Postman (Recommandée)

### Étape 1 : Ouvrir Postman
1. Lancez l'application **Postman** (Desktop ou Web)

### Étape 2 : Sélectionner la Collection
1. Dans le panneau de gauche, trouvez votre collection **WR506D** (ou le nom que vous avez donné)
2. Cliquez sur les **3 points** (⋯) à côté du nom de la collection
3. Ou faites un **clic droit** sur la collection

### Étape 3 : Exporter
1. Sélectionnez **Export** dans le menu
2. Choisissez la version :
   - **Collection v2.1** (recommandé - format standard)
   - **Collection v2.0** (ancien format)
3. Cliquez sur **Export**
4. Choisissez l'emplacement et le nom du fichier (ex: `WR506D.postman_collection.json`)
5. Cliquez sur **Save**

### Étape 4 : Vérifier le fichier
Le fichier JSON sera créé avec un format similaire à :
```json
{
  "info": {
    "name": "WR506D",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"admin@test.com\",\n  \"password\": \"password123\"\n}"
            },
            "url": {
              "raw": "http://localhost:8319/auth",
              "host": ["http://localhost:8319"],
              "path": ["auth"]
            }
          }
        }
      ]
    }
  ]
}
```

---

## Méthode 2 : Via l'API Postman (Avancée)

Si vous utilisez Postman CLI ou l'API :

```bash
# Installer Postman CLI (si pas déjà fait)
npm install -g newman

# Exporter via l'API Postman (nécessite une clé API)
curl -X GET \
  'https://api.getpostman.com/collections/{collection_id}' \
  -H 'X-Api-Key: YOUR_API_KEY' \
  -o WR506D.postman_collection.json
```

---

## Méthode 3 : Créer une Collection depuis Zéro

Si vous n'avez pas encore créé la collection dans Postman :

### 1. Créer la Collection
1. Dans Postman, cliquez sur **New** → **Collection**
2. Nommez-la **WR506D**
3. Ajoutez une description si nécessaire

### 2. Ajouter les Requêtes
Créez des dossiers pour organiser :
- **Auth** - Authentification
- **Actors** - CRUD Acteurs
- **Movies** - CRUD Films
- **Categories** - CRUD Catégories
- **Media** - Upload de médias

### 3. Configurer les Variables d'Environnement (Optionnel mais Recommandé)
1. Créez un environnement **WR506D Local**
2. Ajoutez les variables :
   - `base_url` = `http://localhost:8319`
   - `token` = (vide, sera rempli après login)

### 4. Exporter
Suivez la **Méthode 1** ci-dessus pour exporter

---

## 📝 Structure Recommandée de la Collection

```
WR506D Collection
├── Auth
│   └── Login (POST /auth)
├── Actors
│   ├── List Actors (GET /api/actors)
│   ├── Get Actor (GET /api/actors/{id})
│   ├── Create Actor (POST /api/actors)
│   ├── Update Actor (PATCH /api/actors/{id})
│   └── Delete Actor (DELETE /api/actors/{id})
├── Movies
│   ├── List Movies (GET /api/movies)
│   ├── Get Movie (GET /api/movies/{id})
│   ├── Create Movie (POST /api/movies)
│   ├── Update Movie (PATCH /api/movies/{id})
│   └── Delete Movie (DELETE /api/movies/{id})
├── Categories
│   ├── List Categories (GET /api/categories)
│   ├── Get Category (GET /api/categories/{id})
│   ├── Create Category (POST /api/categories)
│   ├── Update Category (PATCH /api/categories/{id})
│   └── Delete Category (DELETE /api/categories/{id})
└── Media
    ├── Upload Media (POST /api/media_objects)
    ├── List Media (GET /api/media_objects)
    └── Get Media (GET /api/media_objects/{id})
```

---

## ✅ Vérification

Après export, vérifiez que le fichier :
- ✅ A l'extension `.json`
- ✅ Peut être ouvert dans un éditeur de texte
- ✅ Contient la structure JSON valide
- ✅ Peut être réimporté dans Postman (test)

---

## 🔄 Réimporter une Collection

Pour réimporter une collection :
1. Dans Postman : **Import**
2. Sélectionnez le fichier `.json`
3. Cliquez sur **Import**

---

## 📍 Emplacement Recommandé dans le Projet

Placez le fichier exporté à la racine du projet :
```
WR506D/
├── WR506D.postman_collection.json  ← Ici
├── README.md
├── POSTMAN_GUIDE.md
└── ...
```

Puis ajoutez-le au git :
```bash
git add WR506D.postman_collection.json
git commit -m "docs: add Postman collection export"
git push origin main
```
