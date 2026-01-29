# AiSensy API Test - Troubleshooting Guide

## 🔍 Current Issue: HTTP 404 Error

### Problem Identified:
The logs show `"http_code":404` which means the API endpoint is not found.

### Root Cause:
The AiSensy API endpoint structure might have changed or the base URL is incorrect.

---

## ✅ Solution: Test with cURL First

### Step 1: Verify API Endpoint

Try this cURL command to test the AiSensy API directly:

```bash
curl -X POST \
  https://backend.aisensy.com/campaign/t1/api/v2/sendSessionMessage \
  -H 'Content-Type: application/json' \
  -d '{
    "apiKey": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY3ZWQzYzI3MWZlZmEzMDdlNTI4ODA0ZSIsIm5hbWUiOiJSZWFsdmliZSBPZmZpY2lhbCBOdW1iZXIgKDEwMCkiLCJhcHBOYW1lIjoiQWlTZW5zeSIsImNsaWVudElkIjoiNjY4MjcyN2IyOGQ2NWIxZTliOGZjMjVkIiwiYWN0aXZlUGxhbiI6IkZSRUVfRk9SRVZFUiIsImlhdCI6MTc0MzY1ODQ4MH0.c0icK6if0zSaKtv-ut0iA2lOFOiqSNlo246E6n8crhA",
    "campaignName": "test_campaign",
    "destination": "919876543210",
    "userName": "RealVibe CRM",
    "templateParams": ["Test"],
    "source": "realvibe-crm",
    "media": {},
    "buttons": [],
    "carouselCards": [],
    "location": {}
  }'
```

---

## 📝 Common AiSensy Endpoints:

1. **Session Message** (Most Common):
   - `POST /sendSessionMessage`
   - Used for sending messages within 24-hour window

2. **Template Message**:
   - `POST /sendTemplateMessage`
   - Requires pre-approved templates

3. **Text Message** (Old endpoint - might be deprecated):
   - `POST /sendTextMessage`

---

## 🔧 Fix for CRM:

The issue is likely that AiSensy changed from `/sendTextMessage` to `/sendSessionMessage`.

### Update Required in `services/AiSensyService.php`:

Change line 32 from:
```php
return $this->makeRequest('/sendTextMessage', $data);
```

To:
```php
return $this->makeRequest('/sendSessionMessage', $data);
```

Also update the data structure to match AiSensy's requirements.

---

## 🆘 Quick Workaround:

### Option 1: Check AiSensy Documentation
1. Login to your AiSensy dashboard
2. Go to Settings → API Documentation
3. Find the correct endpoint for sending messages
4. Update the CRM code accordingly

### Option 2: Contact AiSensy Support
- Ask for the correct API endpoint for FREE_FOREVER plan
- Verify your API key has permission to send messages

### Option 3: Test in Postman First
1. Import AiSensy API collection (if available)
2. Test with your API key
3. Confirm which endpoint works
4. Update CRM accordingly

---

## 📞 AiSensy Support:
- Website: https://aisensy.com
- Support Email: support@aisensy.com
- Dashboard: https://app.aisensy.com

---

## Next Steps:
1. Check AiSensy dashboard for correct API endpoint
2. I'll update the code with the correct endpoint
3. Retest the integration
