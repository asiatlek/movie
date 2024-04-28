# Nom du Projet

Ce projet est construit avec le framework Symfony. Il s'agit d'une application web qui [décrire brièvement la fonctionnalité principale de l'application].

## Prérequis

Avant de démarrer, assurez-vous que vous avez satisfait aux prérequis suivants :

- **PHP**: Version 8.0 minimum.
- **Composer**: Version 2.0 ou ultérieure.
- **Base de données**: MySQL ou compatible MariaDB.
- **Symfony CLI**: Recommandé pour un meilleur développement.

## Configuration initiale

### Clonage du dépôt

Pour obtenir une copie locale du projet, clonez le dépôt en utilisant :

```bash
git clone https://exemple.com/chemin/vers/le/depot.git
cd nom_du_projet
```

### Installation des dépendances

Utilisez Composer pour installer les dépendances du projet :

```bash
composer install
```

### Configuration de la base de données

Dupliquez le fichier `.env.example` et renommez-le en `.env`. Ensuite, modifiez les paramètres de connexion à la base de données selon votre environnement :

```env
# .env

###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
###< doctrine/doctrine-bundle ###
```

Assurez-vous de remplacer `db_user`, `db_password`, et `db_name` par vos propres valeurs.

### Création de la base de données

Après avoir configuré le fichier `.env`, créez la base de données :

```bash
php bin/console doctrine:database:create
```

### Migration de la base de données

Appliquez les migrations pour mettre à jour votre base de données :

```bash
php bin/console doctrine:migrations:migrate
```

### Chargement des données de test

Pour peupler la base de données avec des données initiales, utilisez les fixtures :

```bash
php bin/console doctrine:fixtures:load
```

## Démarrage de l'application

Pour démarrer le serveur de développement, vous avez deux options :

1. **Avec Symfony CLI** (Recommandé):
   Si vous avez Symfony CLI installé, vous pouvez simplement lancer :
   ```bash
   symfony server:start
   ```
   Cela démarrera un serveur local à l'adresse `http://localhost:8000`.

2. **Sans Symfony CLI**:
   Utilisez le serveur web interne de PHP en lançant :
   ```bash
   php -S localhost:8000 -t public
   ```
   Assurez-vous que votre navigateur pointe vers `http://localhost:8000`.

Visitez l'URL suivante pour plus de facilité: `http://localhost:8000/api/doc`.