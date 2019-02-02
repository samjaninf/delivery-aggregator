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

Create a `.env` file using `.env.example` as a model

```
$ cp .env.example .env
```

Start the Docker containers (might take a while the first time)

```
$ docker-compose up -d
```

Update the container settings, the config cache and then apply the database migrations and seeds

```
$ docker-compose exec app php artisan key:generate
$ docker-compose exec app php artisan config:cache
$ docker-compose exec app php artisan migrate:fresh --seed
```

Server should now be up, visit `http://localost` and try to login with

```
admin@prova.it
password
```
