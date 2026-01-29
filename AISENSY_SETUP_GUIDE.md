# AiSensy Setup Guide - IMPORTANT

## 🚨 Critical: You MUST Create an API Campaign First!

According to AiSensy documentation, you **cannot** just send messages directly. You **must**:

### Step 1: Create an API Campaign in AiSensy Dashboard

1. **Login to AiSensy:** https://app.aisensy.com
2. **Go to:** Campaigns → Create New Campaign
3. **Select:** API Campaign
4. **Campaign Name:** `realvibe_messages` (or any name you prefer)
5. **Select Template:** Choose a pre-approved WhatsApp template
6. **Set Status:** Live (Active)

### Step 2: Get Your Campaign Name

After creating the campaign, copy the exact campaign name. You'll need this!

### Step 3: Update CRM Configuration

Open `config/aisensy.php` and add:

```php
define('AISENSY_CAMPAIGN_NAME', 'realvibe_messages'); // Your campaign name
```

---

## 📝 Understanding AiSensy API

### How It Works:

1. **Templates Required:** You must use pre-approved WhatsApp templates
2. **Campaign-Based:** Every message is sent through a campaign
3. **Parameters:** Templates have placeholders like `{{1}}`, `{{2}}` that you fill with data

### Example Template:

**Template Name:** `welcome_message`  
**Template Content:** 
```
Hello {{1}}! 

Thank you for your interest in {{2}}. 

We will contact you shortly!
```

**API Call:**
```json
{
  "apiKey": "your_key",
  "campaignName": "realvibe_messages",
  "destination": "919876543210",
  "userName": "John Doe",
  "templateParams": ["John Doe", "Luxury Apartments"],
  "source": "realvibe-crm"
}
```

---

## ✅ Current CRM Setup

The code has been updated to match AiSensy official API format:

**File:** `services/AiSensyService.php`
- ✅ Correct endpoint: `https://backend.aisensy.com/campaign/t1/api/v2`
- ✅ Correct data format (apiKey, campaignName, destination, etc.)
- ✅ Uses `templateParams` array

---

## 🔧 What You Need To Do NOW:

### Option 1: Create Simple Text Template (Recommended)

1. **Go to AiSensy Dashboard:** https://app.aisensy.com
2. **Templates → Create Template**
3. **Create a simple template:**
   - **Name:** `simple_text`
   - **Content:** `{{1}}`
   - **Submit for approval** (usually instant for simple templates)
4. **Create API Campaign:**
   - **Name:** `realvibe_messages`
   - **Select template:** `simple_text`
   - **Set to Live**

### Option 2: Use Existing Approved Template

If you already have approved templates:
1. Check your AiSensy templates
2. Note the template name and parameters
3. Create an API campaign using that template

---

## 🚀 Testing After Setup:

Once you've created the campaign in AiSensy:

1. **Update your code** (if needed) with the campaign name
2. **Refresh** the CRM integrations page
3. **Click "Test AiSensy Connection"**
4. **Enter phone number:** Your WhatsApp number
5. **You should receive the message!**

---

## 📋 Checklist:

- [ ] Logged into https://app.aisensy.com
- [ ] Created/Approved a WhatsApp template
- [ ] Created an API Campaign with that template
- [ ] Set campaign to "Live" status
- [ ] Noted the campaign name
- [ ] Updated CRM configuration (if needed)
- [ ] Tested the connection

---

## 💡 Quick Template Example:

If you just want to test quickly, create this template:

**Name:** `test_message`  
**Category:** Utility  
**Content:**
```
Hello {{1}}!

This is a test message from RealVibe CRM.
```

**Parameters:**
- `{{1}}` = Customer name

Then create an API campaign named `test_campaign` using this template, and update your CRM to use:
```php
$aisensy->sendTextMessage('9876543210', 'John Doe', 'test_campaign');
```

---

## 📞 Need Help?

- **AiSensy Support:** https://go.aisensy.com/support
- **Template Approval:** support@aisensy.com
- **Documentation:** https://wiki.aisensy.com/

---

**IMPORTANT:** The FREE_FOREVER plan has 100 messages. Use them wisely for testing!
