# How to setup workspace with Docker

## Requirements

-   Docker
-   Docker Compose

## Setup

Clone the repository, install required Composer packages and setup correct permissions

```
$ git clone https://github.com/danieledelgiudice/delivery-aggregator.git delivery
$ cd delivery
$ docker run --rm -v $(pwd):/app composer install
$ sudo chown -R $USER:$USER .
```

Start the Docker containers (might take a while the first time)

```
$ docker-compose up -d
```

Now create a `.env` file using `.env.example` as a model and be sure to include the following fields

```
### These are required to connect to the database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=delivery
DB_USERNAME=root
DB_PASSWORD=password

### Uncomment and update these with the right keys
#JWT_SECRET=...
#FIREBASE_API_KEY=...
#FIREBASE_PROJECT_ID=...
```

Update the config cache and then apply the database migrations and seeds

```
$ docker-compose exec app php artisan config:cache
$ docker-compose exec app php artisan migrate --seed
```

Server should now be up, visit `http://localost` and try to login with

```
admin@prova.it
password
```
