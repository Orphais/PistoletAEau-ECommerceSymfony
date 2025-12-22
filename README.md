# Tiramisou - Plateforme E-commerce de Pistolets à Eau

**Projet Symfony** par **Théo Sch.**

## Description

Application e-commerce développée pour la vente de pistolets à eau. Le projet inclut :

-   Catalogue de produits avec catégories et recherche
-   Système de panier avec mises à jour en temps réel
-   Gestion des commandes et suivi
-   Authentification utilisateur
-   Tableau de bord administrateur complet
-   Support multilingue (français/anglais)

## Prérequis

-   PHP >= 8.2
-   Composer
-   MySQL 8.0+
-   Symfony CLI (recommandé)

## 🛠️ Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/Orphais/PistoletAEau-ECommerceSymfony
cd tiramisou
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

Adapter le `.env` si nécessaire.

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données de test

```bash
php bin/console doctrine:fixtures:load
```

Identifiants admin : **admin@tiramisou.com** / **password**

### 6. Installer les assets

```bash
php bin/console assets:install
php bin/console importmap:install
```

### 7. Démarrer le serveur

```bash
symfony server:start
```
