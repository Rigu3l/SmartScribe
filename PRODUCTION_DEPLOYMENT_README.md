# 🚀 SmartScribe Production Deployment Guide

## Overview
This guide provides a smooth, error-free deployment process for SmartScribe to production.

## 📋 Prerequisites

- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.0+
- Web server (Apache/Nginx) with SSL
- SSH access to production server

## 🎯 Quick Deployment (Recommended)

### Step 1: Database Setup
```bash
# Connect to your MySQL server as root/admin
mysql -u root -p < setup_production_db.sql
```

### Step 2: Upload Files
Upload all project files to your production server web root directory.

### Step 3: Configure Environment
```bash
# Copy production environment template
cp .env.production .env

# Edit .env with your production values
nano .env
```

**Required .env changes:**
```env
DB_HOST=localhost
DB_NAME=smartscribe_prod
DB_USER=smartscribe_prod
DB_PASS=YourStrongPassword123!
JWT_SECRET=your_64_character_random_secret_key_here
GOOGLE_GEMINI_API_KEY=your_production_api_key
```

### Step 4: Run Deployment
```bash
# Execute production deployment
php deploy_production.php
```

### Step 5: Verify Installation
```bash
# Check if tables were created
php -r "
require 'api/config/database.php';
$db = getDbConnection();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo 'Created tables: ' . implode(', ', $tables) . '\n';
"
```

## 🔧 Manual Deployment (Alternative)

If you prefer step-by-step control:

### 1. Database User & Schema
```sql
-- Run on MySQL server
CREATE DATABASE smartscribe_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'smartscribe_prod'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON smartscribe_prod.* TO 'smartscribe_prod'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Environment Configuration
```bash
cp .env.production .env
# Edit .env with production values
```

### 3. Database Migration
```bash
# Run the clean production migration
php -r "
require 'api/config/database.php';
\$db = getDbConnection();
\$sql = file_get_contents('production_migration.sql');
\$db->exec(\$sql);
echo 'Migration completed successfully\n';
"
```

## 🔐 Security Checklist

### Pre-Deployment
- [ ] Generate strong 64-character JWT secret
- [ ] Use strong database password (12+ characters, mixed case, numbers, symbols)
- [ ] Configure production API keys
- [ ] Enable SSL/TLS certificate
- [ ] Set file permissions correctly (`755` for directories, `644` for files)

### Database Security
- [ ] Database user has minimal required privileges
- [ ] No test/development data in production
- [ ] Regular backup strategy in place
- [ ] Database server firewall configured

### Application Security
- [ ] `APP_DEBUG=false` in production
- [ ] Error logging configured (not displayed to users)
- [ ] File upload restrictions in place
- [ ] Rate limiting implemented

## 🧪 Testing Deployment

### 1. Database Connection Test
```php
<?php
// test_db.php
require 'api/config/database.php';
try {
    $db = getDbConnection();
    echo "✅ Database connection successful\n";
    $stmt = $db->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    echo "Users table accessible: " . $result['count'] . " records\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
```

### 2. API Endpoint Test
```bash
# Test basic API connectivity
curl -X GET "https://yourdomain.com/api/index.php?resource=dashboard&action=stats" \
  -H "Authorization: Bearer test_token"
```

### 3. Frontend Test
- Access your production URL
- Try user registration
- Test note creation and viewing
- Verify dashboard loads correctly

## 🚨 Troubleshooting

### Common Issues

**Database Connection Failed**
```bash
# Check MySQL service
sudo systemctl status mysql

# Test database connection
mysql -u smartscribe_prod -p smartscribe_prod
```

**Permission Errors**
```bash
# Fix file permissions
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
chown -R www-data:www-data /var/www/html
```

**Migration Errors**
```bash
# Check MySQL error logs
tail -f /var/log/mysql/error.log

# Verify database user permissions
mysql -u smartscribe_prod -p -e "SHOW GRANTS;"
```

## 📊 Monitoring & Maintenance

### Automated Tasks
```bash
# Add to crontab for daily maintenance
0 2 * * * php /var/www/html/cleanup_expired_tokens.php
0 3 * * * mysqldump smartscribe_prod > /backups/smartscribe_$(date +\%Y\%m\%d).sql
```

### Health Checks
- Monitor database connections
- Check disk space usage
- Review error logs regularly
- Monitor API response times

## 🎉 Post-Deployment

1. **Update DNS** to point to production server
2. **Configure SSL certificate** for HTTPS
3. **Set up monitoring** (optional but recommended)
4. **Create backup strategy** documentation
5. **Document production procedures** for your team

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review PHP and MySQL error logs
3. Verify all environment variables are set correctly
4. Test database connectivity manually

---

**Deployment completed successfully?** ✅ Your SmartScribe application is now production-ready!