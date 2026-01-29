# Local Webhook Testing Guide

## Method 1: Direct API Testing (Recommended for Development)

### Using PowerShell (Windows)

**Test WordPress API:**
```powershell
$body = @{
    name = "Test Lead"
    phone = "9876543210"
    email = "test@example.com"
    project_id = 1
    message = "Test from PowerShell"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/Realvibe/api/lead-capture/wordpress-api.php" -Method POST -Body $body -ContentType "application/json"
```

**Test Meta Webhook (GET - Verification):**
```powershell
Invoke-RestMethod -Uri "http://localhost/Realvibe/api/lead-capture/meta-webhook.php?hub.mode=subscribe&hub.verify_token=YOUR_TOKEN&hub.challenge=test123"
```

**Test AiSensy Incoming Message:**
```powershell
$body = @{
    phone = "919876543210"
    message = "Test message from customer"
    messageId = "msg_" + (Get-Date).ToString("yyyyMMddHHmmss")
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/Realvibe/api/webhooks/aisensy-incoming.php" -Method POST -Body $body -ContentType "application/json"
```

**Test AiSensy Status Update:**
```powershell
$body = @{
    messageId = "msg_123456"
    status = "delivered"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/Realvibe/api/webhooks/aisensy-status.php" -Method POST -Body $body -ContentType "application/json"
```

---

## Method 2: Using Postman

### Setup Postman Collection

**1. WordPress Lead Capture:**
- Method: POST
- URL: `http://localhost/Realvibe/api/lead-capture/wordpress-api.php`
- Headers:
  - `Content-Type: application/json`
- Body (raw JSON):
```json
{
    "name": "John Doe",
    "phone": "9876543210",
    "email": "john@example.com",
    "project_id": 1,
    "budget": "50L - 1Cr",
    "message": "Interested in 3BHK",
    "source_url": "https://mywebsite.com/project1"
}
```

**2. Meta Webhook (Lead Capture):**
- Method: POST
- URL: `http://localhost/Realvibe/api/lead-capture/meta-webhook.php`
- Body:
```json
{
    "entry": [{
        "changes": [{
            "value": {
                "leadgen_id": "123456",
                "field_data": [
                    {"values": ["John Doe"]},
                    {"values": ["9876543210"]},
                    {"values": ["john@example.com"]}
                ],
                "custom_field": {
                    "project_id": "1"
                }
            }
        }]
    }]
}
```

**3. AiSensy Incoming Message:**
- Method: POST
- URL: `http://localhost/Realvibe/api/webhooks/aisensy-incoming.php`
- Body:
```json
{
    "phone": "919876543210",
    "message": "STOP",
    "messageId": "msg_test_123",
    "from": "919876543210"
}
```

**4. AiSensy Status:**
- Method: POST
- URL: `http://localhost/Realvibe/api/webhooks/aisensy-status.php`
- Body:
```json
{
    "messageId": "wamid.HBgNOTE5ODc2NTQzMjEwFQIA",
    "status": "read"
}
```

---

## Method 3: Using ngrok (For External Webhooks)

### What is ngrok?
ngrok creates a secure tunnel from the internet to your localhost, allowing external services (Meta, AiSensy) to send webhooks to your local development environment.

### Setup ngrok:

**Step 1: Download ngrok**
- Visit: https://ngrok.com/download
- Download Windows version
- Extract to a folder

**Step 2: Start ngrok**
```powershell
# Navigate to ngrok folder
cd C:\path\to\ngrok

# Start tunnel to localhost
ngrok.exe http 80
```

**Step 3: You'll get a URL like:**
```
Forwarding: https://abc123.ngrok.io -> http://localhost:80
```

**Step 4: Use ngrok URL for webhooks:**
- WordPress API: `https://abc123.ngrok.io/Realvibe/api/lead-capture/wordpress-api.php`
- Meta Webhook: `https://abc123.ngrok.io/Realvibe/api/lead-capture/meta-webhook.php`
- AiSensy Incoming: `https://abc123.ngrok.io/Realvibe/api/webhooks/aisensy-incoming.php`
- AiSensy Status: `https://abc123.ngrok.io/Realvibe/api/webhooks/aisensy-status.php`

**Step 5: Configure in External Services:**
- **Meta Developer Console:** Use ngrok URL for webhook
- **AiSensy Dashboard:** Use ngrok URL for incoming/status webhooks

---

## Method 4: Create Test HTML Page

Create: `C:\xampp\htdocs\test-webhook.html`

```html
<!DOCTYPE html>
<html>
<head>
    <title>Webhook Tester</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .test-section {
            background: #f5f5f5;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .result {
            background: white;
            padding: 10px;
            margin-top: 10px;
            border-left: 4px solid #4CAF50;
        }
        .error {
            border-left-color: #f44336;
        }
    </style>
</head>
<body>
    <h1>🧪 RealVibe CRM - Webhook Tester</h1>
    
    <!-- WordPress API Test -->
    <div class="test-section">
        <h2>1. Test WordPress Lead Capture</h2>
        <button onclick="testWordPressAPI()">Test WordPress API</button>
        <div id="wordpress-result" class="result" style="display:none;"></div>
    </div>
    
    <!-- AiSensy Incoming Test -->
    <div class="test-section">
        <h2>2. Test AiSensy Incoming Message</h2>
        <button onclick="testAiSensyIncoming()">Test Incoming Message</button>
        <div id="aisensy-incoming-result" class="result" style="display:none;"></div>
    </div>
    
    <!-- AiSensy Status Test -->
    <div class="test-section">
        <h2>3. Test AiSensy Status Update</h2>
        <button onclick="testAiSensyStatus()">Test Status Update</button>
        <div id="aisensy-status-result" class="result" style="display:none;"></div>
    </div>
    
    <!-- Meta Webhook Test -->
    <div class="test-section">
        <h2>4. Test Meta Webhook</h2>
        <button onclick="testMetaWebhook()">Test Meta Lead</button>
        <div id="meta-result" class="result" style="display:none;"></div>
    </div>

    <script>
        const BASE_URL = 'http://localhost/Realvibe';
        
        function showResult(elementId, data, isError = false) {
            const el = document.getElementById(elementId);
            el.style.display = 'block';
            el.className = 'result' + (isError ? ' error' : '');
            el.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        }
        
        async function testWordPressAPI() {
            try {
                const response = await fetch(BASE_URL + '/api/lead-capture/wordpress-api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        name: 'Test User',
                        phone: '9876543210',
                        email: 'test@example.com',
                        project_id: 1,
                        message: 'Test message from webhook tester'
                    })
                });
                const data = await response.json();
                showResult('wordpress-result', data, !data.success);
            } catch (error) {
                showResult('wordpress-result', {error: error.message}, true);
            }
        }
        
        async function testAiSensyIncoming() {
            try {
                const response = await fetch(BASE_URL + '/api/webhooks/aisensy-incoming.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        phone: '919876543210',
                        message: 'Test incoming message',
                        messageId: 'msg_' + Date.now()
                    })
                });
                const data = await response.json();
                showResult('aisensy-incoming-result', data, !data.success);
            } catch (error) {
                showResult('aisensy-incoming-result', {error: error.message}, true);
            }
        }
        
        async function testAiSensyStatus() {
            try {
                const response = await fetch(BASE_URL + '/api/webhooks/aisensy-status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        messageId: 'test_msg_123',
                        status: 'delivered'
                    })
                });
                const data = await response.json();
                showResult('aisensy-status-result', data, !data.success);
            } catch (error) {
                showResult('aisensy-status-result', {error: error.message}, true);
            }
        }
        
        async function testMetaWebhook() {
            try {
                const response = await fetch(BASE_URL + '/api/lead-capture/meta-webhook.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        entry: [{
                            changes: [{
                                value: {
                                    leadgen_id: '123456',
                                    field_data: [
                                        {values: ['Test User']},
                                        {values: ['9876543210']},
                                        {values: ['test@example.com']}
                                    ],
                                    custom_field: {
                                        project_id: 1
                                    }
                                }
                            }]
                        }]
                    })
                });
                const data = await response.json();
                showResult('meta-result', data, !data.success);
            } catch (error) {
                showResult('meta-result', {error: error.message}, true);
            }
        }
    </script>
</body>
</html>
```

**Access it:** `http://localhost/test-webhook.html`

---

## Method 5: Check Logs

After testing webhooks, check the log files:

```powershell
# WordPress API logs
Get-Content C:\xampp\htdocs\Realvibe\logs\wordpress-api.log -Tail 20

# Meta webhook logs
Get-Content C:\xampp\htdocs\Realvibe\logs\meta-webhook.log -Tail 20

# AiSensy incoming logs
Get-Content C:\xampp\htdocs\Realvibe\logs\aisensy-incoming.log -Tail 20

# AiSensy status logs
Get-Content C:\xampp\htdocs\Realvibe\logs\aisensy-status.log -Tail 20
```

---

## Quick Test Checklist

### ✅ Before Testing:
- [ ] XAMPP Apache is running
- [ ] MySQL is running
- [ ] Database 'realvibe' exists and has tables
- [ ] At least one Project exists in CRM

### ✅ Test WordPress API:
```powershell
curl -X POST http://localhost/Realvibe/api/lead-capture/wordpress-api.php -H "Content-Type: application/json" -d '{\"name\":\"Test\",\"phone\":\"9876543210\",\"project_id\":1}'
```

**Expected:** `{"success":true,"message":"Lead captured successfully",...}`

### ✅ Verify in CRM:
1. Go to `http://localhost/Realvibe`
2. Login
3. Go to Leads
4. Check for new lead with source "WordPress"

### ✅ Test AiSensy Incoming:
```powershell
curl -X POST http://localhost/Realvibe/api/webhooks/aisensy-incoming.php -H "Content-Type: application/json" -d '{\"phone\":\"919876543210\",\"message\":\"Test\"}'
```

---

## Troubleshooting

### Issue: "Failed to connect"
**Solution:** Make sure XAMPP Apache is running

### Issue: "Database connection failed"
**Solution:** 
1. Check MySQL is running
2. Verify database name is 'realvibe' in config/database.php

### Issue: "Project not found"
**Solution:** Create at least one project in CRM first

### Issue: "Lead not found" (for incoming message test)
**Solution:** Create a lead with that phone number first

### Issue: Can't see logs
**Solution:** Logs folder may not exist. Create manually:
```powershell
New-Item -ItemType Directory -Path "C:\xampp\htdocs\Realvibe\logs" -Force
```

---

## Advanced: Webhook Debugging

Add this to any webhook file temporarily for debugging:

```php
// At the top of the file, after <?php
file_put_contents('debug.log', print_r($_POST, true) . PHP_EOL, FILE_APPEND);
file_put_contents('debug.log', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);
```

Then check `C:\xampp\htdocs\Realvibe\debug.log`

---

## Next Steps

1. **Development:** Use direct testing (Method 1-4)
2. **External Testing:** Use ngrok (Method 3)
3. **Production:** Use actual domain URLs

**Remember:** ngrok URLs change every time you restart it (free version). For permanent testing, upgrade to ngrok paid or deploy to a staging server.
