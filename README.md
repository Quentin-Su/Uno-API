# Uno

Ce dépôt contient le code source de mon projet Symfony. Suivez les étapes ci-dessous pour l'installer sur votre machine.


## Prérequis
Assurez-vous d'avoir les éléments suivants installés sur votre machine :

- Composer (https://getcomposer.org/)
- Symfony CLI (https://symfony.com/download)

## Installation

1. Clonez ce dépôt sur votre machine en utilisant la commande suivante :

```
git clone https://github.com/Quentin-Su/Uno-API.git
```

2. Accédez au répertoire du projet :

```
cd uno-api
```

3. Installez les dépendances avec Composer :
```
composer install
```

4. Authentication
```
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096

openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

5. Créez la base de données et exécutez les migrations :
```
php bin/console doctrine:database:create
php bin/console d:s:u --force
php bin/console doctrine:fixtures:load
```

6. Lancez le serveur de développement Symfony :

```
symfony serve
```

7. Accédez à l'application dans votre navigateur à l'adresse http://localhost:8000.

---

**Note** : Il était prévu d'intégrer un WebSocket dans ce projet, cependant, cette fonctionnalité n'a pas été implémentée avec succès lors de ma première tentative. Toute aide ou contribution dans ce domaine serait appréciée.

## Utilisation des API

Pour tester les API, il est recommandé d'utiliser POSTMAN plutôt que Swagger.
