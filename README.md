# Kit

> 🚀 I am creating this project to verify that my starter kit is working successfully. 

- PHP 8.4
- Symfony 7.3
- API Platform 4.1
- PostgreSQL 17

## Local Setup

```bash
# Start the DB (PostgreSQL) inside Docker
$ docker-compose up -d

# Run Symfony locally
$ composer install
$ symfony server:start

# Prepare the database
$ composer reset-dev-db
```

## Testing

Setup:

```bash
$ composer reset-test-db
```

Static analysis (PHPStan):

```bash
$ composer phpstan
```

Unit and functional tests (PHPUnit):

```bash
$ composer phpunit
# Or target individual classes:
$ composer phpunit -- --filter '\\YourTest'
# Or individual tests:
$ composer phpunit -- --filter 'testYourMethod()'
$ composer phpunit -- --filter '\\YourTest::testYourMethod()'
```

Run all tests and analysis:

```bash
$ composer test
```

## Deploying

### Message Queue

For production deployments, you must run the messenger worker:

```bash
$ bin/console messenger:consume async --limit=50 --time-limit=3600
```

This should be managed via Supervisor or similar process manager.

### Cron Schedule

Add your scheduled tasks here as your project grows.

Example:
- `bin/console app:users:deletion:perform` - every day at 6pm
