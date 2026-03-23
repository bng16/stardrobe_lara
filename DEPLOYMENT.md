# Deployment Guide: Creator Marketplace with Sealed Bid Auction

This guide covers deploying the Creator Marketplace application to production.

## Prerequisites

- PHP 8.3 or higher
- MySQL 8.0 or higher
- Redis 6.0 or higher
- Composer 2.x
- Node.js 18+ and npm
- Web server (Nginx or Apache)
- SSL certificate for HTTPS
- Stripe account
- AWS S3 or Cloudflare R2 account
- Email service (Resend, Mailgun, or AWS SES)

## Server Requirements

### PHP Extensions
```bash
php -m | grep -E 'pdo|mysql|redis|gd|mbstring|xml|curl|zip|bcmath|json|openssl'
```

Required extensions:
- PDO
- pdo_mysql
- redis
- gd
- mbstring
- xml
- curl
- zip
- bcmath
- json
- openssl

### System Packages
```bash
# Ubuntu/Debian
sudo apt-get install -y redis-server supervisor nginx mysql-server

# CentOS/RHEL
sudo yum install -y redis supervisor nginx mysql-server
```

## Initial Setup

### 1. Clone Repository
```bash
cd /var/www
git clone <repository-url> creator-marketplace
cd creator-marketplace
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Node dependencies and build assets
npm install
npm run build
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` with production values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=creator_marketplace
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

# See Email Service Configuration section
MAIL_MAILER=resend
RESEND_API_KEY=your_resend_api_key
MAIL_FROM_ADDRESS=noreply@your-domain.com

# See File Storage Configuration section
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# See Stripe Configuration section
STRIPE_KEY=pk_live_your_publishable_key
STRIPE_SECRET=sk_live_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

ADMIN_ALERT_EMAIL=admin@your-domain.com
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate --force

# Optional: Seed initial admin user
php artisan db:seed --class=AdminUserSeeder
```

### 5. File Permissions
```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/creator-marketplace

# Set permissions
sudo chmod -R 755 /var/www/creator-marketplace
sudo chmod -R 775 /var/www/creator-marketplace/storage
sudo chmod -R 775 /var/www/creator-marketplace/bootstrap/cache
```

### 6. Optimize Application
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

## Laravel Scheduler Setup

The application requires Laravel Scheduler to run automated tasks (auction closing, order expiration).

### Cron Job Configuration

Add this cron entry to run the scheduler every minute:

```bash
# Edit crontab
sudo crontab -e -u www-data

# Add this line
* * * * * cd /var/www/creator-marketplace && php artisan schedule:run >> /dev/null 2>&1
```

### Verify Scheduler
```bash
# List scheduled tasks
php artisan schedule:list

# Expected output should include:
# - auctions:close (every minute)
# - orders:expire (hourly)
```

## Queue Worker Setup

The application uses Redis queues for background jobs (emails, auction closing, payment processing).

### Supervisor Configuration

Create supervisor configuration file:

```bash
sudo nano /etc/supervisor/conf.d/creator-marketplace-worker.conf
```

Add this configuration:

```ini
[program:creator-marketplace-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/creator-marketplace/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/creator-marketplace/storage/logs/worker.log
stopwaitsecs=3600
```

### Start Queue Workers
```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start creator-marketplace-worker:*

# Check status
sudo supervisorctl status creator-marketplace-worker:*
```

### Monitor Queue Workers
```bash
# View worker logs
tail -f /var/www/creator-marketplace/storage/logs/worker.log

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Web Server Configuration

### Nginx Configuration

Create Nginx site configuration:

```bash
sudo nano /etc/nginx/sites-available/creator-marketplace
```

Add this configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/creator-marketplace/public;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Max upload size (for product images)
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/creator-marketplace /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## File Storage Configuration

### AWS S3 Setup

1. Create S3 bucket in AWS Console
2. Configure bucket CORS policy:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["https://your-domain.com"],
        "ExposeHeaders": ["ETag"]
    }
]
```

3. Create IAM user with S3 access
4. Add credentials to `.env`:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

### Cloudflare R2 Setup (Alternative)

1. Create R2 bucket in Cloudflare dashboard
2. Generate API tokens
3. Configure in `.env`:

```env
FILESYSTEM_DISK=r2
R2_ACCESS_KEY_ID=your_access_key
R2_SECRET_ACCESS_KEY=your_secret_key
R2_BUCKET=your-bucket-name
R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://your-public-domain.com
```

4. Update `config/filesystems.php` to add R2 disk configuration.

## Stripe Configuration

### Stripe Account Setup

1. Create Stripe account at https://stripe.com
2. Enable Stripe Connect for creator payouts
3. Get API keys from https://dashboard.stripe.com/apikeys

### Configure Stripe in .env

```env
STRIPE_KEY=pk_live_your_publishable_key
STRIPE_SECRET=sk_live_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

### Stripe Webhook Setup

1. Go to https://dashboard.stripe.com/webhooks
2. Add endpoint: `https://your-domain.com/stripe/webhook`
3. Select events to listen for:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
4. Copy webhook signing secret to `.env`

### Stripe Connect for Creator Payouts

1. Enable Stripe Connect in your Stripe dashboard
2. Configure platform settings
3. Add Connect client ID to `.env`:

```env
STRIPE_CONNECT_CLIENT_ID=ca_your_connect_client_id
```

## Email Service Configuration

### Option 1: Resend (Recommended)

1. Create account at https://resend.com
2. Verify your domain
3. Generate API key
4. Configure in `.env`:

```env
MAIL_MAILER=resend
RESEND_API_KEY=your_resend_api_key
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Creator Marketplace"
```

### Option 2: Mailgun

1. Create account at https://mailgun.com
2. Verify your domain
3. Get API credentials
4. Configure in `.env`:

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your_mailgun_secret
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS=noreply@your-domain.com
```

### Option 3: AWS SES

1. Set up AWS SES in AWS Console
2. Verify your domain
3. Configure in `.env`:

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=noreply@your-domain.com
```

## SSL Certificate Setup

### Using Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal is configured automatically
# Test renewal
sudo certbot renew --dry-run
```

## Monitoring and Logging

### Application Logs

```bash
# View Laravel logs
tail -f /var/www/creator-marketplace/storage/logs/laravel.log

# View queue worker logs
tail -f /var/www/creator-marketplace/storage/logs/worker.log
```

### Log Rotation

Create log rotation configuration:

```bash
sudo nano /etc/logrotate.d/creator-marketplace
```

Add:

```
/var/www/creator-marketplace/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Error Monitoring (Optional)

Consider integrating with error monitoring services:
- Sentry: https://sentry.io
- Bugsnag: https://bugsnag.com
- Rollbar: https://rollbar.com

## Database Backups

### Automated Backup Script

Create backup script:

```bash
sudo nano /usr/local/bin/backup-creator-marketplace.sh
```

Add:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/creator-marketplace"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="creator_marketplace"
DB_USER="your_db_user"
DB_PASS="your_db_password"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make executable and schedule:

```bash
sudo chmod +x /usr/local/bin/backup-creator-marketplace.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
0 2 * * * /usr/local/bin/backup-creator-marketplace.sh >> /var/log/backup-creator-marketplace.log 2>&1
```

## Security Checklist

- [ ] HTTPS enabled with valid SSL certificate
- [ ] `.env` file permissions set to 600
- [ ] Database credentials are strong and unique
- [ ] Redis password configured
- [ ] File upload validation enabled
- [ ] Rate limiting configured
- [ ] CORS policy configured for S3/R2
- [ ] Stripe webhook signature verification enabled
- [ ] Admin alert email configured
- [ ] Error reporting configured (Sentry/Bugsnag)
- [ ] Database backups automated
- [ ] Log rotation configured
- [ ] Firewall rules configured (allow only 80, 443, 22)

## Deployment Checklist

- [ ] Code deployed to server
- [ ] Dependencies installed (composer, npm)
- [ ] Environment variables configured
- [ ] Database migrated
- [ ] File permissions set correctly
- [ ] Laravel caches cleared and rebuilt
- [ ] Cron job for scheduler configured
- [ ] Supervisor queue workers running
- [ ] Nginx/Apache configured and running
- [ ] SSL certificate installed
- [ ] S3/R2 bucket configured
- [ ] Stripe webhooks configured
- [ ] Email service configured and tested
- [ ] Monitoring and logging configured
- [ ] Database backups scheduled

## Updating the Application

```bash
# Navigate to application directory
cd /var/www/creator-marketplace

# Put application in maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
sudo supervisorctl restart creator-marketplace-worker:*

# Bring application back online
php artisan up
```

## Troubleshooting

### Queue Workers Not Processing Jobs

```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart creator-marketplace-worker:*

# Check Redis connection
redis-cli ping
```

### Scheduler Not Running

```bash
# Verify cron job exists
sudo crontab -l -u www-data

# Check scheduler output
php artisan schedule:list

# Test scheduler manually
php artisan schedule:run
```

### File Upload Issues

```bash
# Check storage permissions
ls -la storage/

# Check S3 credentials
php artisan tinker
>>> Storage::disk('s3')->exists('test.txt');
```

### Email Not Sending

```bash
# Check mail configuration
php artisan config:show mail

# Test email sending
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

## Support

For issues or questions:
- Check application logs: `storage/logs/laravel.log`
- Check queue worker logs: `storage/logs/worker.log`
- Review failed jobs: `php artisan queue:failed`
- Contact development team

## Additional Resources

- Laravel Documentation: https://laravel.com/docs
- Laravel Cashier (Stripe): https://laravel.com/docs/billing
- Laravel Queue: https://laravel.com/docs/queues
- Laravel Task Scheduling: https://laravel.com/docs/scheduling
- Stripe Documentation: https://stripe.com/docs
- AWS S3 Documentation: https://docs.aws.amazon.com/s3/
