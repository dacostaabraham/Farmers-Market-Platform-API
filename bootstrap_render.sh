#!/bin/bash
# ============================================================
# bootstrap_render.sh — Script de démarrage pour Render.com
# ============================================================
set -e

echo "🚀 Farmers Market API — Render startup"

# Installer les dépendances PHP (prod only)
composer install --no-dev --optimize-autoloader

# Migrations (--force pour prod)
php artisan migrate --force

# Seed uniquement si la table users est vide
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1 | tr -d '[:space:]')
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "🌱 Running seeders (first deploy)..."
    php artisan db:seed --force
fi

# Cache pour la prod
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Démarrer le serveur sur le port fourni par Render
echo "✅ Starting on port ${PORT:-8000}"
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
