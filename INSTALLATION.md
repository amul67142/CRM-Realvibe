# Installation Guide - RealVibe CRM

## Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache web server with mod_rewrite enabled
- cURL extension enabled
- PDO MySQL extension enabled

## Local Development Setup (XAMPP)

### Step 1: Install XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services

### Step 2: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database named `crm_realvibe`
3. Set collation to `utf8mb4_unicode_ci`
4. Import the database schema:
   - Click on `crm_realvibe` database
   - Go to "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

### Step 3: Configure Application

1. **Database Configuration**
   
   Edit `config/database.php`:
   ```php
   private $host = 'localhost';
   private $dbname = 'crm_realvibe';
   private $username = 'root';
   private $password = ''; // Usually empty for XAMPP
   ```

2. **AiSensy API Configuration**
   
   Edit `config/aisensy.php`:
   ```php
   define('AISENSY_API_KEY', 'your_actual_api_key_here');
   ```
   
   Get your API key from AiSensy dashboard.

3. **Base URL** (Optional)
   
   The application auto-detects the base URL. If needed, you can manually set it in `config/config.php`.

### Step 4: Set Permissions

Ensure the following directories are writable:
- `uploads/`
- `logs/`

On Windows (XAMPP), this is usually automatic. On Linux:
```bash
chmod -R 755 uploads/
chmod -R 755 logs/
```

### Step 5: Access the Application

1. Open browser: `http://localhost/Realvibe`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`
3. **IMPORTANT**: Change the default password immediately!

---

## Production Deployment (Hostinger)

### Step 1: Prepare Files

1. Download all project files as a ZIP
2. Ensure all sensitive credentials are updated

### Step 2: Upload via FTP

1. Connect to Hostinger via FTP (FileZilla recommended)
   - Host: Your domain FTP address
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: 21

2. Upload all files to `public_html` directory
   - If installing in subdirectory, upload to `public_html/realvibe`

### Step 3: Create Database

1. Login to Hostinger hPanel
2. Go to **Databases** → **MySQL Databases**
3. Create new database (e.g., `u123456_crm`)
4. Create database user and set password
5. Assign user to database with all privileges
6. Note down: database name, username, password, and host

### Step 4: Import Database

1. In hPanel, go to **phpMyAdmin**
2. Select your database
3. Click **Import**
4. Upload `database/schema.sql`
5. Click **Go**

### Step 5: Configure Application

1. Edit `config/database.php` via FTP or File Manager:
   ```php
   private $host = 'localhost'; // or specific host provided by Hostinger
   private $dbname = 'u123456_crm'; // your actual database name
   private $username = 'u123456_user'; // your database username
   private $password = 'your_database_password';
   ```

2. Edit `config/aisensy.php`:
   ```php
   define('AISENSY_API_KEY', 'your_actual_api_key');
   ```

3. Update webhook URLs in `config/aisensy.php`:
   ```php
   define('AISENSY_WEBHOOK_STATUS_URL', 'https://yourdomain.com/realvibe/api/webhooks/aisensy-status.php');
   define('AISENSY_WEBHOOK_INCOMING_URL', 'https://yourdomain.com/realvibe/api/webhooks/aisensy-incoming.php');
   ```

4. Set environment to production in `config/config.php`:
   ```php
   define('ENVIRONMENT', 'production');
   ```

### Step 6: Set File Permissions

Via File Manager or FTP, set permissions:
- Directories: 755
- PHP files: 644
- `uploads/` directory: 755 (writable)
- `logs/` directory: 755 (writable)

### Step 7: Configure SSL

1. In Hostinger hPanel, install free SSL certificate
2. Force HTTPS in `.htaccess` (uncomment these lines):
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### Step 8: Set Up Cron Jobs

1. In Hostinger hPanel, go to **Advanced** → **Cron Jobs**
2. Add new cron job:
   
   **For campaign messages (hourly)**:
   - Type: Command Line
   - Command: `/usr/bin/php /home/USERNAME/public_html/realvibe/cron/send-campaign-messages.php`
   - Minute: `0`
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
   
   This runs every hour at minute 0.

### Step 9: Configure Webhooks

#### Meta/Facebook Webhook
1. Go to Meta Developer Console
2. Navigate to your app → Webhooks
3. Add callback URL: `https://yourdomain.com/realvibe/api/lead-capture/meta-webhook.php`
4. Set verify token (update in `meta-webhook.php`)
5. Subscribe to `leadgen` events

#### AiSensy Webhooks
1. Login to AiSensy dashboard
2. Go to Settings → Webhooks
3. Set incoming message webhook: `https://yourdomain.com/realvibe/api/webhooks/aisensy-incoming.php`
4. Set status webhook: `https://yourdomain.com/realvibe/api/webhooks/aisensy-status.php`

### Step 10: Test the Application

1. Access your domain: `https://yourdomain.com/realvibe`
2. Login with admin/admin123
3. Change password immediately
4. Test lead creation
5. Test webhook endpoints (use tools like Postman)
6. Verify cron job execution in logs

---

## Post-Installation Security Checklist

- [ ] Changed default admin password
- [ ] Updated database credentials
- [ ] Configured AiSensy API key
- [ ] Enabled SSL/HTTPS
- [ ] Set proper file permissions
- [ ] Configured webhooks
- [ ] Set up cron jobs
- [ ] Tested webhook endpoints
- [ ] Verified cron job execution
- [ ] Checked error logs
- [ ] Restricted database user privileges (only necessary permissions)
- [ ] Backed up database

---

## Troubleshooting

### Database Connection Error
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check if PDO MySQL extension is enabled

### 500 Internal Server Error
- Check Apache error logs
- Verify .htaccess is correctly configured
- Ensure mod_rewrite is enabled

### Cron Job Not Running
- Check cron job command path is correct
- Verify PHP CLI path (`/usr/bin/php` or `/usr/local/bin/php`)
- Check cron logs in hPanel

### Webhooks Not Working
- Verify webhook URLs are accessible publicly
- Check webhook logs in `logs/` directory
- Ensure SSL is properly configured
- Test with curl or Postman

### WhatsApp Messages Not Sending
- Verify AiSensy API key is correct
- Check AiSensy account balance
- Review logs in `logs/aisensy.log`
- Ensure phone numbers are in correct format (919876543210)

---

## Updating the Application

1. Backup database and files
2. Upload new files via FTP (don't overwrite config files)
3. Run any new database migrations if provided
4. Clear browser cache
5. Test functionality

---

## Support

For technical support, contact your system administrator or refer to the logs in the `logs/` directory for debugging information.
