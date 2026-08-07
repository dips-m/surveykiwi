# SurveyIQ

## Requirements

- Docker Desktop
- Docker Compose

## Run

```bash
docker compose up --build -d
```

# Configuration

Before running the application, update the following configuration values.

## 1. Cookie Salt

Open:

```
application/bootstrap.php
```

Set a unique cookie salt:

```php
Cookie::$salt = 'your_random_cookie_salt_here';
```

Example:

```php
Cookie::$salt = 'replace_with_a_long_random_secure_string';
```

> **Important:** Do not use the example value in production. Generate your own secure random string.

---

## Database Configuration

Copy the example configuration:

```bash
cp application/config/database.php.example application/config/database.php
```

Open:

```
application/config/database.php
```

Update the database credentials:

```php
'connection' => array(
    'dsn'        => 'mysql:host=db;dbname=surveykiwi;charset=utf8',
    'username'   => 'YOUR_DB_USERNAME',
    'password'   => 'YOUR_DB_PASSWORD',
    'persistent' => FALSE,
),
```

### Docker Development Defaults

| Setting | Value |
|---------|-------|
| Host | `db` |
| Port | `3306` |
| Database | `surveykiwi` |
| Username | `root` |
| Password | `root` |

---

## Security Notes

- Never commit production database credentials.
- Never commit a production cookie salt.
- Replace all placeholder values before deploying to production.

---

## Application

http://localhost:8080

## phpMyAdmin

http://localhost:8081