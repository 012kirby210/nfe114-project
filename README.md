# projet NFE 114
## Messagerie asynchrone et communication temps réel

## Installation du projet :
Pour faire fonctionner le projet, vous aurez besoin de 
* docker
* composer
* yarn/npm


:speech_balloon: Depuis la racine du projet. 
* cloner le projet :
```sh
git clone https://github.com/012kirby210/nfe114-project
```
* installer les dépendances php : 
```sh
composer install
```
* copier le fichier .env-dist
```sh
cp .env-dist .env
```
* construire et fetch les images docker : 
```sh
cd nfe114Docker/DockerFiles
docker build -t php7.3-fpm-xdebug ./
cd ..
docker-compose up
```
* Une fois la construction de l'image effectuée et les services en route, construire la base de données :
depuis le répertoire : nfe114Docker
```sh
docker-compose exec php_fpm /app/bin/console doctrine:database:create
```
Si cela renvoie une erreur, essayer de mettre à jour docker, ou renseigner l'adresse ip du service :
```sh
docker-compose exec database_service /bin/bash
apt update
apt install net-tools
ifconfig
```
Noter la valeur de l'adresse ip de l'eth0 et renseignez la dans le fichier .env à la place de host.docker.internal. 
* Renseignez une adresse d'envoi de courriel valide dans ce même fichier .env,  
une ligne utilisant gmail ressemble à MAILER_DSN=smtp://LENOMDUCOMPTE@gmail.com:LEMOTDEPASSEDUCOMPTE@smtp.gmail.com:587
* installer les modules nodes :
```sh
yarn install
```
* Invoquer la compilation des assets : 
```sh 
yarn encore dev
```


Si tout c'est bien passé, le serveur devrait servir l'application à l'adresse http://localhost:8000.  
L'url d'enregistrement est http://localhost:8000/register
En renseignant un adresse d'email valide pour le compte, dans la boite mail associé, uen fois le compte enregistré, vous drevriez recevoir un courriel de validation.  
En cliquant sur le lien de validation, vous devriez revenir sur le site.  

La page de login est http://localhost:8000/login.  
En renseignant email/mot de passe, vous devriez accéder à la page profil.  

:question: Si l'upload de fichier image ne fonctionne pas, il vous faudra donner les droits d'accès en écriture au répertoire public/uploads.


