# Amipro

Amipro uses FuelPHP and Knockout.js, and can be set up with a local environment in Docker.

---

## Requirements

- Docker & Docker Compose
- Git
- Browser（Chrome / Firefox recommended）

---

## Docker setup

1. Download the repository, unzip the folder, and move your terminal window to the amipro directory.

2. Build the Docker container and images and perform the migrations:

```bash
docker compose up -d --build
docker compose exec app php oil refine migrate
docker compose exec app php oil refine session
```

- When prompted for the last command, please select "create".

---

## Database

- Uses MySQL.
- Settings can be changed in FuelPHP's `db.php` file.
- When you want to see the contents of the database:

```bash
docker compose exec db mysql -u amipro_user -p amipro_db
```

- The password is amipro_pass

---

## Local access

After setting up Docker, access through your browser:
```http://localhost:8080```

- The port can be configured in the docker-compose.yml file.

---