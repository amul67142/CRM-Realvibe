# WordPress Lead Capture Integration Guide

## Overview
This guide will help you integrate your WordPress landing pages with the RealVibe CRM to automatically capture leads.

## Integration Methods

### Method 1: Contact Form 7 (Recommended)

**Step 1: Install Contact Form 7**
- Install the "Contact Form 7" plugin from WordPress
- Install "Contact Form CFDB7" to store submissions

**Step 2: Create Your Contact Form**

Create a form with these fields:
```
<label> Your Name (required)
    [text* your-name] </label>

<label> Phone Number (required)
    [tel* your-phone] </label>

<label> Email
    [email your-email] </label>

<label> Budget
    [text your-budget placeholder "e.g., 50L - 1Cr"] </label>

<label> Message
    [textarea your-message] </label>

[submit "Submit Inquiry"]
```

**Step 3: Add JavaScript to Your Theme**

Add this code to your theme's `functions.php` or use a plugin like "Code Snippets":

```php
// Add custom JavaScript for lead capture
function add_crm_integration_script() {
    ?>
    <script>
    document.addEventListener('wpcf7mailsent', function(event) {
        // Get form data
        var formData = new FormData(event.target);
        
        // Prepare data for CRM
        var crmData = {
            name: formData.get('your-name'),
            phone: formData.get('your-phone'),
            email: formData.get('your-email'),
            budget: formData.get('your-budget'),
            message: formData.get('your-message'),
            project_id: 1, // CHANGE THIS to your project ID
            source_url: window.location.href
        };
        
        // Send to CRM
        fetch('https://yourdomain.com/Realvibe/api/lead-capture/wordpress-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(crmData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Lead captured:', data);
            if (data.success) {
                // Optional: Track conversion
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'generate_lead', {
                        'event_category': 'Lead',
                        'event_label': crmData.project_id
                    });
                }
            }
        })
        .catch(error => {
            console.error('CRM Error:', error);
        });
    }, false);
    </script>
    <?php
}
add_action('wp_footer', 'add_crm_integration_script');
```

---

### Method 2: Elementor Forms

**Step 1: Create Elementor Form**
- Add Form widget with fields: Name, Phone, Email, Message
- Set field IDs: `name`, `phone`, `email`, `message`

**Step 2: Add Custom Code**

In **Elementor > Custom Code** or theme's footer:

```javascript
<script>
jQuery(document).ready(function($) {
    $(document).on('submit_success', function(event, data) {
        // Prepare CRM data
        var crmData = {
            name: data.fields.name,
            phone: data.fields.phone,
            email: data.fields.email,
            message: data.fields.message,
            project_id: 1, // CHANGE THIS
            source_url: window.location.href
        };
        
        // Send to CRM
        $.ajax({
            url: 'https://yourdomain.com/Realvibe/api/lead-capture/wordpress-api.php',
            type: 'POST',
            data: JSON.stringify(crmData),
            contentType: 'application/json',
            success: function(response) {
                console.log('Lead captured:', response);
            },
            error: function(error) {
                console.error('CRM Error:', error);
            }
        });
    });
});
</script>
```

---

### Method 3: WPForms

**Step 1: Create WPForm**
- Create form with Name, Phone, Email fields
- Install "WPForms Webhooks" addon

**Step 2: Configure Webhook**
- In form settings, add webhook
- URL: `https://yourdomain.com/Realvibe/api/lead-capture/wordpress-api.php`
- Method: POST
- Map fields:
  - `name` → Name field
  - `phone` → Phone field
  - `email` → Email field
  - `project_id` → Set to your project ID (static value)

---

### Method 4: Gravity Forms

**Step 1: Install Gravity Forms Webhooks Add-on**

**Step 2: Create Feed**
- Forms > Your Form > Settings > Webhooks
- Request URL: `https://yourdomain.com/Realvibe/api/lead-capture/wordpress-api.php`
- Request Method: POST
- Request Format: JSON
- Map fields to: `name`, `phone`, `email`, `message`, `project_id`

---

### Method 5: Custom HTML Form (Any Theme)

```html
<form id="lead-capture-form">
    <input type="text" name="name" placeholder="Your Name" required>
    <input type="tel" name="phone" placeholder="Phone Number" required>
    <input type="email" name="email" placeholder="Email">
    <input type="text" name="budget" placeholder="Budget (optional)">
    <textarea name="message" placeholder="Your Message"></textarea>
    <button type="submit">Submit</button>
</form>

<script>
document.getElementById('lead-capture-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var data = {
        name: formData.get('name'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        budget: formData.get('budget'),
        message: formData.get('message'),
        project_id: 1, // CHANGE THIS to your project ID
        source_url: window.location.href
    };
    
    fetch('https://yourdomain.com/Realvibe/api/lead-capture/wordpress-api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Thank you! We will contact you soon.');
            this.reset();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>
```

---

## Getting Your Project ID

1. Login to RealVibe CRM
2. Go to **Projects**
3. Create or select your project
4. The ID is in the URL: `projects/edit?id=1` (1 is the project ID)
5. Use this ID in your WordPress integration code

---

## Testing the Integration

### Step 1: Test Locally
```bash
curl -X POST http://localhost/Realvibe/api/lead-capture/wordpress-api.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Lead",
    "phone": "9876543210",
    "email": "test@example.com",
    "project_id": 1,
    "message": "Test message from WordPress"
  }'
```

### Step 2: Check Response
Success response:
```json
{
  "success": true,
  "message": "Lead captured successfully",
  "lead_id": 123
}
```

Error response:
```json
{
  "success": false,
  "error": "duplicate",
  "message": "This phone number is already registered"
}
```

### Step 3: Verify in CRM
- Login to CRM
- Go to **Leads**
- Check if the test lead appears with source "WordPress"

---

## Multiple Projects Setup

If you have multiple WordPress landing pages for different projects:

**Option 1: Different Forms**
```javascript
// Form 1 - Project ID: 1
project_id: 1

// Form 2 - Project ID: 2
project_id: 2
```

**Option 2: Hidden Field**
Add a hidden field in your form:
```html
<input type="hidden" name="project_id" value="1">
```

**Option 3: URL Parameter**
```javascript
// Get project ID from URL
var urlParams = new URLSearchParams(window.location.search);
var projectId = urlParams.get('pid') || 1; // Default to 1

crmData.project_id = projectId;
```

Then use URLs like: `https://yoursite.com/landing-page/?pid=2`

---

## Troubleshooting

### Issue: CORS Error
**Solution:** The API already has CORS headers. If still failing, add to `.htaccess`:
```apache
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
```

### Issue: 404 Not Found
**Solution:** Check the API URL is correct and accessible

### Issue: No Welcome Message Sent
**Solution:** 
1. Check AiSensy API is configured in Settings > Integrations
2. Ensure project has a welcome message set

### Issue: Duplicate leads not being detected
**Solution:** The system checks phone + project combination. Same phone for different projects is allowed.

---

## Advanced: Zapier Integration

If you want to use Zapier:

1. **Create Zap:** WordPress Form → Webhook
2. **Webhook URL:** `https://yourdomain.com/Realvibe/api/lead-capture/wordpress-api.php`
3. **Method:** POST
4. **Data:** Map form fields to `name`, `phone`, `email`, `project_id`, `message`

---

## Security Best Practices

1. **Use HTTPS:** Always use HTTPS in production
2. **Rate Limiting:** Consider adding rate limiting to prevent spam
3. **Validate Project ID:** Make sure the project_id exists
4. **Honeypot Field:** Add honeypot to prevent bot submissions

---

## Support

For issues or questions:
- Check logs: `Realvibe/logs/wordpress-api.log`
- Verify project exists in CRM
- Test with curl command first
- Check browser console for JavaScript errors
