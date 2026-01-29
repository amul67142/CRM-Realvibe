# RealVibe CRM - Real Estate Lead Management System

## Overview

RealVibe CRM is a comprehensive lead management system designed specifically for real estate businesses. It captures leads from multiple sources (Meta/Facebook, WordPress, LinkedIn), stores them centrally, and nurtures them through automated 5-day WhatsApp campaigns using the AiSensy API.

## Features

- **Multi-Client & Project Management** - Support multiple real estate clients and projects
- **Multi-Source Lead Capture** - Capture leads from Meta (Facebook/Instagram), WordPress, LinkedIn, and manual entry
- **WhatsApp Automation** - Send welcome messages and automated 5-day nurture campaigns
- **Conversation Tracking** - Full WhatsApp conversation history with delivery status
- **Campaign Management** - Create and manage multi-day nurturing campaigns
- **Analytics Dashboard** - Real-time statistics and trends
- **Role-Based Access** - Admin, Manager, and Agent roles with appropriate permissions
- **Subscription Management** - Automatic STOP/START keyword detection
- **Duplicate Prevention** - Prevent duplicate leads per project

## Tech Stack

- **Backend**: PHP 8.1+ (Pure MVC, no frameworks)
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **UI Framework**: Tailwind CSS + DaisyUI
- **Database**: MySQL 8.0
- **WhatsApp API**: AiSensy
- **Server**: Apache with mod_rewrite
- **Deployment**: Hostinger Shared Hosting (FTP)

## Project Structure

```
Realvibe/
├── config/               # Configuration files
│   ├── config.php
│   ├── database.php
│   └── aisensy.php
├── controllers/          # MVC Controllers
├── models/              # Data models
├── views/               # PHP views
│   ├── layouts/
│   ├── dashboard/
│   ├── auth/
│   └── ...
├── services/            # Business logic services
│   ├── AiSensyService.php
│   ├── CampaignService.php
│   └── NotificationService.php
├── api/                 # Webhook endpoints
│   ├── lead-capture/
│   └── webhooks/
├── cron/                # Scheduled jobs
├── includes/            # Helper functions
├── assets/              # CSS, JS, images
├── uploads/             # User uploads
├── logs/                # Application logs
├── database/            # SQL schemas
├── index.php            # Front controller
└── .htaccess           # Apache configuration
```

## Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed setup instructions.

### Quick Start (Local Development)

1. **Clone or download the project** to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\Realvibe
   ```

2. **Create MySQL database**:
   - Database name: `crm_realvibe`
   - Import `database/schema.sql`

3. **Configure database connection**:
   Edit `config/database.php`:
   ```php
   private $host = 'localhost';
   private $dbname = 'crm_realvibe';
   private $username = 'root';
   private $password = '';
   ```

4. **Configure AiSensy API**:
   Edit `config/aisensy.php` and add your API key:
   ```php
   define('AISENSY_API_KEY', 'your_actual_api_key_here');
   ```

5. **Access the application**:
   ```
   http://localhost/Realvibe
   ```

6. **Login with default credentials**:
   - Username: `admin`
   - Password: `admin123`
   - **⚠️ Change this immediately after first login!**

## Default credentials

**Username**: admin  
**Password**: admin123

## Key Components

### Models
- `User.php` - User authentication and management
- `Client.php` - Real estate client/developer management
- `Project.php` - Real estate project management
- `Lead.php` - Lead management with duplicate detection
- `Campaign.php` - Campaign management
- `CampaignMessage.php` - Campaign message scheduling
- `LeadCampaign.php` - Lead enrollment tracking
- `WhatsAppMessage.php` - Message delivery tracking
- `LeadReply.php` - Incoming message management
- `MessageTemplate.php` - Reusable templates

### Services
- `AiSensyService.php` - WhatsApp API integration
- `CampaignService.php` - Campaign business logic
- `NotificationService.php` - Internal notifications

### API Endpoints
- `/api/lead-capture/meta-webhook.php` - Facebook/Instagram webhook
- `/api/lead-capture/wordpress-api.php` - WordPress form integration
- `/api/webhooks/aisensy-incoming.php` - Incoming WhatsApp messages
- `/api/webhooks/aisensy-status.php` - Message delivery status updates

### Cron Jobs
- `cron/send-campaign-messages.php` - Process and send due campaign messages (run hourly)

## Configuration

### Environment
Edit `config/config.php` to change environment between development and production:
```php
define('ENVIRONMENT', 'development'); // or 'production'
```

### Database
Configure database credentials in `config/database.php`

### AiSensy API
Add your AiSensy API key in `config/aisensy.php`:
- API Key
- Template IDs (for pre-approved templates)
- Webhook URLs (after deployment)

### Webhooks
After deployment, configure webhook URLs in respective platforms:
- **Meta Webhook**: `https://yourdomain.com/Realvibe/api/lead-capture/meta-webhook.php`
- **AiSensy Incoming**: `https://yourdomain.com/Realvibe/api/webhooks/aisensy-incoming.php`
- **AiSensy Status**: `https://yourdomain.com/Realvibe/api/webhooks/aisensy-status.php`

## Cron Job Setup

For automated campaign messages, set up a cron job on your hosting:

**Command**:
```bash
/usr/bin/php /path/to/your/installation/cron/send-campaign-messages.php
```

**Schedule**: Every hour
```
0 * * * *
```

## Security Features

- CSRF token protection on all forms
- Prepared statements for SQL injection prevention
- Password hashing with bcrypt
- Session timeout after inactivity
- Role-based access control
- File upload validation
- .htaccess protection for sensitive directories

## Merge Tags

Available for message templates:
- `{{name}}` - Lead's full name
- `{{first_name}}` - Lead's first name
- `{{phone}}` - Lead's phone number
- `{{email}}` - Lead's email address
- `{{project_name}}` - Project name
- `{{project_location}}` - Project location
- `{{price_range}}` - Project price range
- `{{client_name}}` - Developer/client name
- `{{current_date}}` - Current date
- `{{brochure_link}}` - Project brochure URL

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

Proprietary - All rights reserved

## Support

For issues and feature requests, contact your system administrator.

---

**© 2026 RealVibe CRM. All rights reserved.**
