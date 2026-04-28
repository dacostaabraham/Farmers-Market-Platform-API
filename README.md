# 🌱 Farmers Market API — Laravel Backend

Backend RESTful pour la plateforme de marché agricole (Côte d'Ivoire).  
Construit avec **Laravel 11**, **PHP 8.2**, **Laravel Sanctum**.

---

## 📋 Fonctionnalités

- ✅ Auth via Sanctum (token-based)
- ✅ Rôles : Admin → Supervisor → Operator
- ✅ Catalogue produits avec catégories imbriquées (N niveaux)
- ✅ Gestion agriculteurs avec limite de crédit
- ✅ Transactions cash et crédit (intérêt configurable)
- ✅ Enforcement de limite de crédit
- ✅ Remboursement FIFO par matière première (cacao, etc.)
- ✅ Taux de conversion configurable
- ✅ Seeders avec données réalistes

---

## 🚀 Installation locale (sans Docker)

### Prérequis
- PHP 8.1+
- Composer
- MySQL 8.0+

```bash
# 1. Cloner et installer
git clone https://github.com/VOTRE_USERNAME/farmers-market-api.git
cd farmers-market-api
composer install

# 2. Configuration
cp .env.example .env
php artisan key:generate

# 3. Configurer la base de données dans .env
# DB_DATABASE=farmers_market
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 4. Migrations + seeding
php artisan migrate --seed

# 5. Lancer le serveur
php artisan serve
```

L'API sera disponible sur `http://localhost:8000`

---

## 🐳 Installation avec Docker (recommandé)

```bash
git clone https://github.com/VOTRE_USERNAME/farmers-market-api.git
cd farmers-market-api

# Copier l'env
cp .env.example .env

# Démarrer les containers
docker-compose up -d

# Générer la clé app
docker-compose exec app php artisan key:generate

# Migrations + seed
docker-compose exec app php artisan migrate --seed
```

- API : http://localhost:8000
- phpMyAdmin : http://localhost:8080

---

## 🌐 Déploiement sur Railway (gratuit)

Le fichier `railway.json` et `bootstrap_railway.sh` sont déjà inclus dans ce repo.

### Étapes

**1. Préparer le repo GitHub**
```bash
git init
git add .
git commit -m "feat: initial commit"
git remote add origin https://github.com/TON_USERNAME/farmers-market-api.git
git push -u origin main
```

**2. Créer le projet Railway**
- Aller sur [railway.app](https://railway.app) → **New Project**
- Choisir **Deploy from GitHub repo** → sélectionner `farmers-market-api`

**3. Ajouter MySQL**
- Dans le projet Railway : **+ Add Service → Database → MySQL**
- Railway crée automatiquement les variables `MYSQL_HOST`, `MYSQL_PORT`, etc.

**4. Configurer les variables d'environnement**

Dans Railway → ton service Laravel → **Variables** :

| Variable | Valeur |
|----------|--------|
| `APP_ENV` | `production` |
| `APP_KEY` | *(générer localement : `php artisan key:generate --show`)* |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://TON-APP.up.railway.app` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQL_HOST}}` |
| `DB_PORT` | `${{MySQL.MYSQL_PORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQL_DATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQL_USER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQL_PASSWORD}}` |

**5. Déployer**
- Le déploiement se lance automatiquement
- Le script `bootstrap_railway.sh` : exécute les migrations, seed si première fois, démarre le serveur
- L'URL publique apparaît dans **Settings → Domains**

**6. Mettre à jour Flutter**

Dans `lib/core/constants/app_constants.dart` :
```dart
static const String baseUrl = 'https://TON-APP.up.railway.app/api';
```

---

## 🔑 Identifiants de test (après seed)

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@farmersmarket.ci | password123 |
| Supervisor | supervisor@farmersmarket.ci | password123 |
| Operator | operator@farmersmarket.ci | password123 |

> **Note iOS Simulator** : remplacer `localhost` par `127.0.0.1` dans `AppConstants.baseUrl` du projet Flutter. Ajouter `NSAppTransportSecurity` dans `Info.plist` pour autoriser le HTTP local.

---

## 📡 Endpoints principaux

### Auth
```
POST /api/auth/login
POST /api/auth/logout  [auth]
GET  /api/auth/me      [auth]
```

### Utilisateurs
```
GET/POST   /api/users/supervisors   [admin]
GET/PATCH  /api/users/operators     [admin, supervisor]
```

### Produits & Catégories
```
GET    /api/categories/tree           [all]
GET    /api/categories                [all]
POST   /api/categories                [admin, supervisor]
GET    /api/categories/{id}/products  [all]
GET    /api/products                  [all]
POST   /api/products                  [admin, supervisor]
```

### Agriculteurs
```
GET    /api/farmers/search?q=...  [all]
GET    /api/farmers               [all]
POST   /api/farmers               [all]
GET    /api/farmers/{id}          [all]
```

### Transactions
```
POST  /api/transactions   [all]
GET   /api/transactions   [all]
GET   /api/transactions/{id}
```

### Dettes & Remboursements
```
GET   /api/farmers/{id}/debts
POST  /api/repayments             [commodity_kg + farmer_id]
POST  /api/repayments?preview=1   [aperçu conversion avant confirmation]
```

### Paramètres
```
GET  /api/settings           [admin]
PUT  /api/settings/{key}     [admin]
```

---

## ⚙️ Paramètres configurables

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `credit_interest_rate` | 30 | Taux d'intérêt crédit (%) |
| `commodity_rate_fcfa` | 1000 | Taux de conversion cacao (FCFA/kg) |

---

## 🧠 Logique métier clé

### Crédit
```
total = subtotal × (1 + interest_rate / 100)
```

### Enforcement limite crédit
```
if (current_debt + new_credit_total > credit_limit) → BLOCKED (422)
```

### Remboursement FIFO
```
open_debts.orderBy('created_at')
  → for each debt: apply payment → update remaining_balance
```

---

## 🗂️ Structure du projet
```
app/
├── Http/
│   ├── Controllers/   # AuthController, TransactionController, etc.
│   ├── Middleware/    # CheckRole.php
│   └── Requests/      # Form validation
├── Models/            # User, Farmer, Product, Transaction, Debt, Repayment
└── Services/          # TransactionService, RepaymentService
database/
├── migrations/        # 9 migrations
└── seeders/           # DatabaseSeeder avec données réalistes
routes/
└── api.php            # Toutes les routes groupées par rôle
```
