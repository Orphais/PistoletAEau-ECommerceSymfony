#!/usr/bin/env bash
set -e

echo "==> Déploiement en production..."

# 1. Dépendances sans packages dev, autoloader optimisé
echo "==> Installation des dépendances..."
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Migrations base de données
echo "==> Migrations base de données..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# 3. Compilation des assets (AssetMapper → public/assets/)
echo "==> Compilation des assets..."
php bin/console asset-map:compile --env=prod

# 4. Vide et préchauffe le cache de prod
echo "==> Préchauffage du cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

echo "==> Déploiement terminé."
