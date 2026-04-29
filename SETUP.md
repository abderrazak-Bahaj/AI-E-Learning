# Laravel 13 API Kit - Setup Guide

## Configuration Summary

This Laravel 13 application has been configured for the CoursePalette e-learning platform migration.

### Environment Configuration

The `.env` file has been configured with the following settings:

#### Application Settings

- **APP_NAME**: CoursePalette
- **APP_ENV**: local
- **APP_DEBUG**: true
- **APP_URL**: http://localhost:8000

#### Database Configuration (PostgreSQL)

- **DB_CONNECTION**: pgsql
- **DB_HOST**: postgres (Docker service name)
- **DB_PORT**: 5432
- **DB_DATABASE**: laravel
- **DB_USERNAME**: laravel
- **DB_PASSWORD**: secret

#### Mail Configuration (Brevo SMTP)

- **MAIL_MAILER**: smtp
- **MAIL_HOST**: smtp-relay.brevo.com
- **MAIL_PORT**: 587
- **MAIL_ENCRYPTION**: tls
- **MAIL_USERNAME**: your_brevo_username (needs to be updated)
- **MAIL_PASSWORD**: your_brevo_api_key (needs to be updated)
- **MAIL_FROM_ADDRESS**: noreply@yourdomain.com (needs to be updated)

#### PayPal Configuration

- **PAYPAL_MODE**: sandbox
- **PAYPAL_CLIENT_ID**: (needs to be filled)
- **PAYPAL_CLIENT_SECRET**: (needs to be filled)
- **FRONTEND_URL**: http://localhost:5173

#### reCAPTCHA Configuration

- **RECAPTCHA_SITE_KEY**: 6LfuhOsmAAAAAFV4EzOmWqvvgcc-kk0wUy-oYiKb
- **RECAPTCHA_SECRET_KEY**: 6LfuhOsmAAAAABK6tNrW_VGihsGt3akUflwgpFsa

#### CORS Configuration

- **CORS_ALLOWED_ORIGINS**: http://localhost:5173,http://localhost:8081,https://course-palette.vercel.app

### Docker Configuration

The `docker-compose.yml` has been updated to use PostgreSQL instead of MySQL:

#### Services

- **app**: PHP 8.3-FPM with PostgreSQL PDO driver
- **nginx**: Web server (port 8000)
- **postgres**: PostgreSQL 16 Alpine (port 5432)
- **adminer**: Database management UI (port 8080)
- **mailhog**: Email testing (port 8025)
- **redis**: Redis cache (port 6379)

### Getting Started

#### 1. Start Docker Containers

```bash
docker-compose up -d --build
```

This will:

- Build the PHP container with PostgreSQL support
- Start PostgreSQL database
- Start Nginx web server
- Start Redis cache
- Start Adminer for database management
- Start MailHog for email testing

#### 2. Install Dependencies

```bash
docker-compose exec app composer install
```

#### 3. Run Migrations

```bash
docker-compose exec app php artisan migrate
```

#### 4. Verify Application

Access the application at: http://localhost:8000

#### 5. Database Management

Access Adminer at: http://localhost:8080

- System: PostgreSQL
- Server: postgres
- Username: laravel
- Password: secret
- Database: laravel

#### 6. Email Testing

Access MailHog at: http://localhost:8025

### Next Steps

1. **Update Credentials**: Fill in the actual credentials for:
    - Brevo SMTP (MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS)
    - PayPal (PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET)

2. **Install Passport**: Run task 1.1.2 to install and configure Laravel Passport for OAuth2 authentication

3. **Install Additional Packages**: Run task 1.1.3 to install DomPDF, PayPal SDK, and L5 Swagger

### Troubleshooting

#### Database Connection Issues

If you encounter database connection issues:

1. Ensure PostgreSQL container is running: `docker-compose ps`
2. Check PostgreSQL logs: `docker-compose logs postgres`
3. Verify database credentials in `.env` match docker-compose.yml

#### Permission Issues

If you encounter permission issues:

```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

#### Clear Cache

```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
```

### Configuration Files Modified

- ✅ `.env` - Updated with PostgreSQL, Brevo, PayPal, reCAPTCHA, and CORS settings
- ✅ `.env.example` - Updated to reflect new configuration
- ✅ `docker-compose.yml` - Replaced MySQL with PostgreSQL
- ✅ `Dockerfile` - Added PostgreSQL PDO driver support

### Status

**Task 1.1.1: Configure Laravel 13 with Kite** - ✅ COMPLETED

All environment variables have been configured. The application is ready for:

- Database connection testing
- Passport OAuth2 installation (Task 1.1.2)
- Additional package installation (Task 1.1.3)
