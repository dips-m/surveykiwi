# SurveyKiwi

SurveyKiwi is a survey management application built with **PHP 5.6, Kohana 3.3.0, MariaDB 10.5, Apache, Bootstrap 3, and jQuery**.

It handles survey creation and scheduling, participant import and management, and automated email notifications to both survey participants and survey creators.

## What Was Delivered

| Task ID | Description | Commit |
|---|---|---|
| Task-001 | Initial project setup | [dda03d6](https://github.com/dips-m/surveykiwi/commit/dda03d6d08859d68690265b074dd776bc50ae8a6) |
| Task-002 | Initialize Kohana 3.3 project with Docker and database connectivity | [432b1cd](https://github.com/dips-m/surveykiwi/commit/432b1cd866ed5707c03b02b313a0be2b46313f5f) |
| Task-003 | Database schema design & seeder | [b2c4f16](https://github.com/dips-m/surveykiwi/commit/b2c4f1658dc9fde5d9c6ece7ac4b6c5a07925d05) |
| Task-004 | Dashboard creation and survey list with view details page | [f0f02cb](https://github.com/dips-m/surveykiwi/commit/f0f02cbb528396ab0e305283cbaef0307860db6c) |
| Task-005 | Survey schedule configuration | [ab00d1b](https://github.com/dips-m/surveykiwi/commit/ab00d1b03a28f6158873d42f4e1dbe4f3bdf56cc) |
| Task-006 | Import participants | [a662842](https://github.com/dips-m/surveykiwi/commit/a6628423d498428907ed4888e1189f1a9fdc10f3) |
| Task-007 | Schedule list | [7651f1f](https://github.com/dips-m/surveykiwi/commit/7651f1f28409b955135104c15c8b63ad2ca82aa9) |
| Task-008 | Participants list and actions | [5d0a2f8](https://github.com/dips-m/surveykiwi/commit/5d0a2f8961dcbf5edc72fa053ffadd8d945e5ed5) |
| Task-009 | Cron job to automate email notification for participants | [74f4ac1](https://github.com/dips-m/surveykiwi/commit/74f4ac1f58629c173182334ed89e58b38c7f5505) |
| Task-010 | Reminder notification to survey creator | [17ffc8c](https://github.com/dips-m/surveykiwi/commit/17ffc8cba3c50f1ce4f50d15d9ec266df28c929c) |
| Task-011 | Show past and future schedule | [02d9e21](https://github.com/dips-m/surveykiwi/commit/02d9e21ad00e52ea089793a40c720a7d774bb52a) |

## Prerequisites and Versions Used

| Component | Version |
|---|---|
| PHP | 5.6 |
| Kohana Framework | 3.3.0 |
| MariaDB | 10.5 |
| Web server | Apache |
| Frontend | Bootstrap 3, jQuery |
| Docker Desktop | Latest |
| Git | Latest |
| WSL 2 | Required if using Windows |

Third-party PHP/JS libraries used are listed in [Third-Party Libraries](#third-party-libraries) below.

## Setup Instructions

Follow these steps in order, from clone to a running application.

### 1. Clone repository

```bash
git clone https://github.com/dips-m/surveykiwi.git
cd surveykiwi
git submodule init
git submodule update
```

### 2. Start Docker containers

```bash
docker compose up -d --build
```

This builds and starts the application container (`surveykiwi_app`) and database container (`surveykiwi_db`). The database and schema are created automatically as part of this step — no manual `CREATE DATABASE` step is required.

### 3. Load the database schema and seed data

The SQL files are located at:

```text
database/schema.sql
database/seed.sql
```

Docker Compose creates the database automatically on first `up`. If you need to (re)load the SQL manually — for example, after changing the schema or resetting the database — run:

```bash
docker exec -i surveykiwi_db mysql -u root -p surveykiwi < database/schema.sql
docker exec -i surveykiwi_db mysql -u root -p surveykiwi < database/seed.sql
```

You will be prompted for the database password set in `application/config/database.php` (see step 4).

### 4. Configure database connection

Copy the example config file, copy `database.example` to `database`:

```bash
cp application/config/database.php.example application/config/database.php
```

```text
application/config/database.php
```
```php
'connection' => array(
    'dsn'        => 'mysql:host=db;dbname=surveykiwi;charset=utf8',
=======
Update the connection credentials:

```php
'connection' => array(
    'hostname'   => 'surveykiwi_db',
    'database'   => 'surveykiwi',
>>>>>>> 6bc7269 (Fix:database schema, update CSS, add sample participants and README)
    'username'   => 'root',
    'password'   => 'YOUR_DATABASE_PASSWORD',
    'persistent' => FALSE,
),
``` 
Replace `YOUR_DATABASE_PASSWORD` with the MySQL/MariaDB root password configured in your Docker setup.

### 5. Configure cookie salt

Open:

```text
application/bootstrap.php
```

Set a cookie salt:

```php
Cookie::$salt = 'YOUR_RANDOM_COOKIE_SALT';
```

Replace `YOUR_RANDOM_COOKIE_SALT` with any random string (e.g. `openssl rand -hex 16`).

### 6. Configure SMTP and notification settings

Open:

```text
application/config/email.php
```

Update the SMTP credentials:

```php
'smtp' => array(
    'host'     => 'sandbox.smtp.mailtrap.io',
    'username' => 'YOUR_SMTP_USERNAME',
    'password' => 'YOUR_SMTP_PASSWORD',
    'port'     => 2525,
    'timeout'  => 30,
),

'from' => array(
    'email' => 'no-reply@surveykiwi.test',
    'name'  => 'SurveyKiwi',
),

'creator_email' => 'john@example.com',
```

Replace:

- `YOUR_SMTP_USERNAME` with your SMTP username.
- `YOUR_SMTP_PASSWORD` with your SMTP password.
- `john@example.com` with the email address that should receive survey creator notifications.

### 7. Open the application

Application:

```text
http://localhost:8080
```

Database admin (Adminer/phpMyAdmin):

```text
http://localhost:8081/index.php
```

No login is required — the application is accessible directly at the URL above.

## Implementation Approach

The application was built incrementally, task by task, on top of the existing Kohana 3.3 codebase rather than as a rewrite:

- **Foundation first (Task-001–003):** project scaffolding, Docker-based local environment (app + MariaDB containers), and the core schema/seed data were established before any feature work, so every later task could be built and tested against a running, seeded instance.
- **Core survey workflows (Task-004–008):** dashboard, survey listing/detail, schedule configuration, and participant management (import via CSV, list/add/edit/delete) were built as standard Kohana MVC controllers/views, following the existing project's naming and directory conventions rather than introducing new patterns.
- **Notifications (Task-009–010):** email sending was centralized in a single `Service_Mailer` class wrapping PHPMailer, with one shared internal `_send()` routine used by both participant invitations and creator notifications to avoid duplicated SMTP/error-handling logic. A CLI task (`--task=notifications`) drives both flows so it can run on a schedule (cron) in production. Delivery outcomes are persisted per record (e.g. `creator_email_sent`) so re-running the task doesn't resend notifications that already succeeded.
- **Schedule visibility (Task-011):** extended the existing schedule list query to include both past and upcoming entries rather than only future ones, keeping the same list view/controller rather than introducing a separate page.

Throughout, changes were kept minimal and scoped to each task rather than refactoring unrelated code, and existing conventions (Kohana ORM/query builder, Bootstrap 3 UI components, view/controller structure) were reused so the codebase stays consistent for future maintainers.

## Running and Verifying Features

Below is how to exercise each delivered task against its acceptance criteria.

### Task-001 / Task-002 — Project setup, Kohana + Docker

- Run `docker compose up -d --build`, then open `http://localhost:8080`.
- **Acceptance criteria:** the application loads without errors, confirming Kohana 3.3 is running through Docker and connected to MariaDB.

### Task-003 — Database schema & seeder

- Open the database admin at `http://localhost:8081/index.php` and inspect the `surveykiwi` database.
- **Acceptance criteria:** required tables and relationships exist, and seed data is present in the tables after running `database/schema.sql` and `database/seed.sql`.

### Task-004 — Dashboard & survey list

- Navigate to the dashboard at `http://localhost:8080`.
- **Acceptance criteria:**
  - Dashboard displays survey statistics.
  - Survey list is visible and each survey can be opened to view its details.

### Task-005 — Survey schedule configuration

- Open a survey and configure its schedule.
- **Acceptance criteria:** you can set a start date, end date, and frequency for the survey, and the values save correctly.

### Task-006 — Import participants

- From a survey, use the participant import option and upload a CSV file of participants.
- A sample CSV file is provided at:

  ```text
  database/sample/survey_participants-2026.csv
  ```

- **Acceptance criteria:** participants from the CSV are imported and appear in the participant list.

### Task-007 — Schedule list

- Navigate to the schedule list view.
- **Acceptance criteria:** generated survey schedule entries are listed and viewable.

### Task-008 — Participants list and actions

- Open the participants list for a survey.
- **Acceptance criteria:** you can list, add, edit, and delete participants, and each action reflects immediately in the list.

### Task-009 — Participant email notifications (cron)

- Run the notification task manually:

  ```bash
  docker exec surveykiwi_app php /var/www/html/index.php --task=notifications
  ```

- **Acceptance criteria:**
  - Survey invitation emails are sent to participants for due schedule entries.
  - Each attempt records a sent/failed status.
- Verify delivery in your configured SMTP provider (e.g. Mailtrap inbox) and confirm the sent/failed status in the database (`survey_schedule_entries` / participant records).

### Task-010 — Creator reminder notification

- Ensure a survey schedule entry has a start time within the next hour, then run the same notification task:

  ```bash
  docker exec surveykiwi_app php /var/www/html/index.php --task=notifications
  ```

- **Acceptance criteria:**
  - The survey creator (configured via `creator_email` in `application/config/email.php`) receives exactly one notification within the hour before the survey's start time.
  - The notification status is recorded (e.g. `creator_email_sent`) so the notification is not sent more than once for the same schedule entry.
- Verify delivery in your SMTP provider and confirm the recorded status in the database.

### Task-011 — Past and future schedule

- Navigate to the schedule list view.
- **Acceptance criteria:** both past and future (upcoming) survey schedule entries are shown.

## Notification Task Summary

The notification task (`--task=notifications`) handles:

- Survey participant invitations
- Failed invitation retry
- Survey creator "starting soon" notification (once, within 1 hour of survey start)

## Third-Party Libraries

| Library | Version | License |
|---|---|---|
| PHPMailer | 6.8.1 | MIT |
| Flatpickr | 4.6.13 | MIT |
| Bootstrap | 3.x | MIT |
| jQuery | Bundled with Bootstrap 3 setup | MIT |