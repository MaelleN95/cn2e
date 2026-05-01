# CN2E - Symfony 7.4 Dockerized Project

This repository contains a Symfony 7.4 project named **cn2e-app**, fully containerized using Docker with PHP 8.4, MariaDB, and Apache.

## Project Structure

```php
cn2e/
├── cn2e-app/ # Symfony application
├── docker/ # Docker configurations (PHP, Apache, MariaDB)
├── docker-compose.yml # Docker Compose orchestration file
├── .gitignore # Git ignore rules
└── README.md
```

## Usage

1. Clone the repo and navigate to the project root :

   ```bash
   git clone https://github.com/MaelleN95/cn2e
   cd cn2e
   ```

2. Build and start the Docker containers :

   ```bash
   docker-compose up -d --build
   ```

3. (Optional) Access the PHP container to run Symfony CLI or Composer commands :
   
   ```bash
   docker exec -it cn2e-php bash
   ```

4. Access the Symfony app at `http://localhost:8080`

5. Access phpMyAdmin at `http://localhost:8081`
   - Username: `symfony`
   - Password: `symfony`

To stop the containers :

```bash
docker-compose down
```

## Database management and fixtures

> To use these commands, you need to be in the Docker container.

Create the database and apply migrations :
```bash
php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate
```

Load fixtures (**warning: resets data**) :
```bash
php bin/console doctrine:fixtures:load
```

If you wish to delete the database completely:
```bash
php bin/console doctrine:database:drop --force
```

## Notes

- Database credentials are defined in `docker-compose.yml` and Symfony `.env`.