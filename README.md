
# Event Management API

Event Management API designed for handling event registrations, attendee bookings, and user authentication. It is containerized with Docker and uses MySQL as the database.

---

## Features

- Laravel (Latest version)
- RESTful API for Events & Attendees
- API Token Authentication (via Sanctum)
- MySQL (Dockerized)
- Fully tested with PHPUnit

---

## Project Setup

### Requirements

- Docker installed on your machine

---

### Installation Steps

1. **Clone the repository**:

```bash
git clone https://github.com/shivagurubalaji/event-booking-api.git
cd event-booking-api
```

2. **Create the main `.env` file in project root**:

```dotenv
DB_DATABASE=laravel
MYSQL_ROOT_PASSWORD=root
DB_USERNAME=laravel
DB_PASSWORD=secret
```

3. **Set up Laravel app environment**:

Navigate into the Laravel app:

```bash
cd src/laravel/
cp .env.example .env
```

Then open `.env` and make sure it contains the following:

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:8zlSTJjS2YDmsP/Mk1dpxRMMUJFbqSwaH31ZyD8/5fc=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql-db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

SESSION_DRIVER=database

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

---

### Run Docker Containers

From the project **root** folder:

```bash
docker-compose up -d --build
```

---

### 🔧 Setup Laravel Inside Docker

Run the following commands **one by one**:

```bash
docker exec -it laravel-app bash
```

Inside the container:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=UserSeeder
```

---

### Authentication

After running the seeder, a token will be printed to your console.

**Copy the token** and use it in the postman collection (Download - https://dqrv2.nyc3.cdn.digitaloceanspaces.com/Laravel-Event-Management-API-Server.postman_collection.json) as a **Bearer Token**.

```http
Authorization: Bearer <your-token-here>
```

---

## 📬 Base URL

All API requests should use:

```
http://localhost:80
```

---