# Clinical Healthcare Appointment System

A PHP 8.2+ MVC-style appointment-management portfolio project with patient, doctor, receptionist, and administrator workflows.

## Features

- Role-based authentication and authorization.
- Appointment booking, cancellation, and schedule conflict checks.
- Doctor schedules and availability exceptions.
- Patient and staff management, notifications, and audit events.
- SQLite and MySQL/MariaDB database support.
- PHPUnit tests for core workflows.
- Responsive, accessible portal and clinic landing-page experience.

## Interface and assets

The interface uses a restrained ink, evergreen, and warm-neutral visual system, with reduced-motion-aware section reveals and responsive navigation. Local clinic and care-team photography is served from `public/images/` and sourced from [Unsplash](https://unsplash.com/license) under the Unsplash License. The images are included locally, so page rendering does not require an external image CDN.

## Local development

Requirements: PHP 8.2+, Composer, and the PDO driver for your chosen database. PHPUnit also requires PHP's `mbstring` extension.

1. Copy `.env.example` to `.env` and keep the local-development settings. The example enables demo records only for development.
2. Install dependencies:
   ```sh
   composer install
   ```
3. Run the application:
   ```sh
   php -S localhost:8000 -t public public/index.php
   ```
4. Sign in with the synthetic demo accounts in `database/seeders/001_demo_seed.sql`. The demo password is `password123`; these accounts must never be enabled on a public/production deployment.
5. Run tests:
   ```sh
   composer test
   ```

Migrations are versioned and applied once per database by the application. SQLite files are stored outside the web root; the configured directory must be writable by PHP.

## Portfolio deployment checklist

- Configure the web server's **document root as the repository's `public/` directory**, never the repository root. Apache's front-controller and sensitive-file rules are in `public/.htaccess`; ensure `mod_rewrite` is enabled and overrides are permitted. For Nginx, configure equivalent front-controller routing and deny requests for `.sqlite`, `.db`, `.sql`, `.env`, `.log`, and backup files.
- Install production dependencies with `composer install --no-dev --optimize-autoloader`.
- Set `APP_ENV=production`, `APP_DEBUG=false`, a correct HTTPS `APP_URL`, and `DB_SEED_DEMO_DATA=false`. Demo seeding is disabled in production even if accidentally requested. Never publish the credentials in the development seed.
- Set database credentials through the host's secret/environment configuration. For SQLite, use a persistent, writable path outside `public/`; the app rejects SQLite locations inside the web root. For MySQL/MariaDB, use a dedicated database and a least-privilege account.
- Apply HTTPS at the hosting platform or reverse proxy. Production session cookies are always marked `Secure`; do not expose an HTTP endpoint for authenticated traffic.
- Provision the first administrator once, after configuring the production database. Supply `INITIAL_ADMIN_PASSWORD` through a secret manager/environment variable (minimum 16 characters), then run:
  ```sh
  php scripts/create-admin.php admin@example.com "Portfolio Administrator"
  ```
  The command refuses to create a second administrator or overwrite an existing user. Remove the bootstrap password from the environment immediately afterward.
- Allow the application to write only to required storage/log locations. Keep database files, environment files, backups, and logs outside `public/`; configure backups, retention, and monitoring at the host.
- Run the test suite and perform a deployment smoke test (login, role restrictions, appointment booking/cancellation, logout, and database persistence) using only fabricated data.

## Deploying the live demo on Render

This repository includes a Docker-based Render Blueprint at `render.yaml`. Because the app stores its demo database on a persistent disk, the Blueprint uses Render's paid `starter` web-service plan; Render does not provide persistent disks for free web services. Review Render's current pricing before creating the service.

1. Push this repository to GitHub without `.env`, `storage/app.sqlite`, `vendor/`, or local test output.
2. In Render, choose **New → Blueprint**, connect the GitHub repository, review the proposed `aurelia-clinic-appointment-system` web service, and apply it. The Dockerfile installs the required PHP PDO drivers and Apache serves only `public/`.
3. Wait for `/health` to report healthy. Render provisions a 1 GB persistent disk at `/var/data`; the application creates and migrates `/var/data/app.sqlite` on first start. Demo seeding remains disabled.
4. Create the first administrator from the Render service Shell. Set a unique password in the shell session (do not save it in GitHub or the Blueprint), then run the one-time provisioning script:
   ```sh
   read -s -p "Initial admin password: " INITIAL_ADMIN_PASSWORD; echo
   export INITIAL_ADMIN_PASSWORD
   php scripts/create-admin.php admin@example.com "Portfolio Administrator"
   unset INITIAL_ADMIN_PASSWORD
   ```
   The script refuses to overwrite an existing account or create another initial administrator.
5. Provision a synthetic patient, doctor, receptionist, schedule, and future appointment for the live demo:
   ```sh
   read -s -p "Patient demo password (16+ characters): " PORTFOLIO_DEMO_PASSWORD; echo
   export PORTFOLIO_DEMO_PASSWORD
   php scripts/create-portfolio-demo.php
   unset PORTFOLIO_DEMO_PASSWORD
   ```
   The generated doctor and receptionist credentials are never printed; only the patient account password is eligible for optional public demo access. Share that patient-only password only if you intend to let visitors use the shared synthetic account, and rotate it if needed. Never publish administrator credentials.
6. Copy the generated HTTPS service URL into the portfolio link. Recheck patient sign-in, appointment booking/cancellation, role restrictions, logout, and persistent data after a redeploy.

The Render disk is attached to a single service instance, so this SQLite portfolio deployment is intentionally single-instance. For multi-instance traffic or real operational use, move to a managed database and obtain a separate privacy/security/compliance review.

## Scope and safety

This is a portfolio/demo application, **not a medical device or a production clinical system**. Use fabricated data only. Do not enter real patient, health, or other sensitive personal information. A real healthcare deployment requires a separate security, privacy, regulatory, operational, and backup review.
