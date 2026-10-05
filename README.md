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

## Deploying the live demo on HelioHost

HelioHost Johnny is a no-hosting-fee option for a **fabricated-data portfolio demo only**. Its official documentation lists PHP 8.2+ and MariaDB. Free SSL is available through Plesk. Johnny has limited signup availability, requires a monthly Plesk login to avoid inactivity suspension, and states a 97% uptime goal with no SLA or service/data-safety guarantees. Read the [Johnny limits](https://wiki.helionet.org/Johnny), [HelioHost terms](https://heliohost.org/terms/), and [PHP extension list](https://wiki.helionet.org/PHP) before creating an account. Do not use this service for real patient data.

### Create the account and database

1. Sign up for one Johnny account at [heliohost.org/signup](https://heliohost.org/signup/) when the registration window is open. One account per person is allowed. Account activation may take up to two hours.
2. In Plesk, create one MariaDB database and a user restricted to that database. Use `localhost`, keep remote database access disabled, and save its generated credentials somewhere private.
3. In Plesk's PHP settings, select PHP 8.2 or newer and verify that **PDO MySQL** is enabled in `phpinfo()`. The PHP configuration differs by server; do not proceed if the required extension is absent.
4. Enable the free Let's Encrypt certificate in Plesk's **SSL It!** settings and enforce HTTPS for the website.

### Upload the project with a private document root

Use Plesk File Manager or SFTP (HelioHost recommends SFTP, not plain FTP). Keep the source code and secrets above/outside the public `httpdocs` directory. Arrange the account approximately as follows, replacing `<account-home>` with the actual directory above `httpdocs`:

```text
<account-home>/
├── .env
├── app/
├── config/
├── database/
├── vendor/
├── views/
└── httpdocs/
    ├── .htaccess
    ├── index.php
    ├── css/
    ├── images/
    └── js/
```

Upload the repository's application directories and the already-installed local `vendor/` directory alongside `httpdocs`; upload only the **contents** of `public/` into `httpdocs/`. Keep `scripts/` outside `httpdocs/` too. Do not upload `.git/`, `.env.example`, tests, local SQLite files, PHPUnit cache, or internal agent/prompt notes. Composer is not required on the hosting server when the prepared `vendor/` directory is uploaded; HelioHost disables process-launch functions such as `exec` and `proc_open`.

Create `.env` at `<account-home>/.env`, not in `httpdocs`, using these production settings and the exact database name, username, and password Plesk assigned:

```dotenv
APP_NAME="Aurelia Private Clinic"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-heliohost-domain

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=plesk_assigned_database
DB_USERNAME=plesk_assigned_database_user
DB_PASSWORD=replace-with-private-database-password
DB_SEED_DEMO_DATA=false

SESSION_SECURE=true
SESSION_SAME_SITE=Lax
```

Use the free HelioHost subdomain or a domain you control; verify HTTPS works before enabling the live demo. Set `.env` to owner-only readable permissions where Plesk allows.

### Initialize the app and demo accounts

The first web request runs the versioned MySQL migrations. Then provision the administrator and synthetic demo accounts using the PHP CLI **only if the account gives you a supported shell or PHP scheduled-task facility**. Keep `scripts/` outside `httpdocs/`, use one-time unique passwords, save the generated credentials privately, and never put passwords into a public page or GitHub:

```sh
INITIAL_ADMIN_PASSWORD='use-a-unique-password-of-at-least-16-characters' \
  php scripts/create-admin.php admin@example.test "Portfolio Administrator"
PORTFOLIO_DEMO_PASSWORD='use-a-different-unique-password-of-at-least-16-characters' \
  php scripts/create-portfolio-demo.php
```

The portfolio script creates fictional patient, doctor, and receptionist accounts with a future confirmed sample appointment. If the free account does **not** provide a private supported way to run PHP CLI tasks, stop and ask before provisioning: do not expose an unauthenticated setup script or put credentials in a public web route.

After setup, sign in at `/login` and exercise the appointment flow with the synthetic patient account. Publish only the patient demo email/password if visitors should share that account; never share the administrator, doctor, or reception credentials. Re-provisioning is intentionally blocked to avoid silently overwriting accounts.

HelioHost is a best-effort shared service, not an SLA-backed or healthcare-compliant host. Keep off-host backups, expect occasional downtime, log into Plesk at least monthly, and use synthetic data only.

## Deploying the live demo on Render

This repository includes a Docker-based Render Blueprint at `render.yaml`, configured for Render's free web-service plan. Free services have an ephemeral filesystem, so the SQLite database and all account changes are lost when the service spins down, restarts, or redeploys. A free service also spins down after 15 minutes without traffic; its next request may take about a minute while it starts. See Render's [free instance limitations](https://render.com/docs/free) and [persistent disk availability](https://render.com/docs/disks).

1. Push this repository to GitHub without `.env`, `storage/app.sqlite`, `vendor/`, or local test output.
2. In Render, choose **New → Blueprint**, connect the GitHub repository, review the proposed `aurelia-clinic-appointment-system` web service, and apply it. The Dockerfile installs the required PHP PDO drivers and Apache serves only `public/`.
3. During the initial Blueprint setup, enter a unique patient demo password of at least 16 characters for `PORTFOLIO_DEMO_PASSWORD`. Render stores it as an environment secret; don't commit it to GitHub. The startup script creates a fictional patient, doctor, receptionist, schedule, and future appointment when the temporary database is empty. It creates no administrator account and does not log demo passwords.
4. Wait for `/health` to report healthy, then sign in using `patient.demo@example.test` and the password you supplied to Render. Publish the patient demo password only if you intentionally want visitors to use the shared synthetic account; never publish staff or administrator credentials.
5. Copy the generated HTTPS service URL into the portfolio link. Check sign-in, appointment booking/cancellation, role restrictions, and logout. Expect any user or appointment changes to disappear after a spin-down or redeploy, and the first visit after inactivity to have a cold-start delay.

The free service's included hours are shared across your Render workspace and can run out; check Render's current usage and billing pages. This configuration is intended for a low-traffic, synthetic-data portfolio demonstration, not persistent records or real operational/healthcare use.

## Scope and safety

This is a portfolio/demo application, **not a medical device or a production clinical system**. Use fabricated data only. Do not enter real patient, health, or other sensitive personal information. A real healthcare deployment requires a separate security, privacy, regulatory, operational, and backup review.
