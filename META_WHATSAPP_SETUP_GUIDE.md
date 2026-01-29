# Meta WhatsApp Cloud API - Complete Setup Guide

## 🎯 What You'll Get

- ✅ **1,000 FREE messages per month** (forever!)
- ✅ Official WhatsApp Business API
- ✅ Most reliable platform
- ✅ Direct integration (no third party)

---

## 📋 Prerequisites

Before starting, you need:
- [ ] Facebook account
- [ ] Business email address
- [ ] Phone number for WhatsApp Business
- [ ] 1-2 hours of time

---

## 🚀 Step-by-Step Setup

### **Step 1: Create Facebook Business Account** (10 minutes)

1. **Go to:** https://business.facebook.com/
2. **Click:** "Create Account"
3. **Fill in:**
   - Business name: "RealVibe CRM" (or your company name)
   - Your name
   - Business email
4. **Verify email**
5. **Done!** ✅

---

### **Step 2: Create Meta Developer App** (15 minutes)

1. **Go to:** https://developers.facebook.com/
2. **Click:** "My Apps" → "Create App"
3. **Select:** "Business" as app type
4. **Fill in:**
   - **App Name:** "RealVibe WhatsApp Integration"
   - **App Contact Email:** Your email
   - **Business Account:** Select the one you created
5. **Click:** "Create App"
6. **Done!** ✅

---

### **Step 3: Add WhatsApp Product** (5 minutes)

1. **In your app dashboard:**
   - Scroll to "Add Products to Your App"
   - Find **"WhatsApp"**
   - Click **"Set Up"**

2. **Quick Start appears:**
   - Click **"Start Using the API"**

3. **Done!** ✅

---

### **Step 4: Get Your Credentials** (10 minutes)

#### A. Get Temporary Access Token (for testing):

1. **In WhatsApp → Getting Started:**
   - Look for **"Temporary access token"**
   - Click **"Generate"** or copy the existing one
   - **SAVE THIS TOKEN** (valid for 24 hours)

#### B. Get Phone Number ID:

1. **Still in Getting Started:**
   - Under "Send and receive messages"
   - Look for **"Phone number ID"**
   - **COPY THIS NUMBER** (looks like: `123456789012345`)

#### C. Get WhatsApp Business Account ID:

1. **In left sidebar:**
   - Click **"WhatsApp" → "Getting Started"**
   - Look for **"WhatsApp Business Account ID"**
   - **COPY THIS NUMBER**

#### D. Create Permanent Access Token:

1. **In left sidebar:**
   - Click **"Tools" → "Graph API Explorer"**
2. **User or Page:** Select your WhatsApp Business Account
3. **Permissions:** Add these:
   - `whatsapp_business_messaging`
   - `whatsapp_business_management`
4. **Generate Access Token**
5. **Click "Generate Access Token"**
6. **SAVE THIS TOKEN** (this is your permanent token)

---

### **Step 5: Verify Your Business** (Optional but Recommended)

1. **Go to:** Business Settings → Security Center
2. **Start Verification:**
   - Upload business documents
   - Provide business details
3. **Wait for approval** (1-3 days)

**Note:** You can test without verification, but there's a limit of 50 unique phone numbers.

---

### **Step 6: Configure in Your CRM** (5 minutes)

1. **Open:** http://localhost/Realvibe/settings/integrations
2. **Scroll to:** "WhatsApp Business API" section
3. **Fill in:**
   - **API Key/Access Token:** Your permanent access token
   - **API URL:** `https://graph.facebook.com/v18.0`
   - **Phone Number ID:** Your Phone Number ID
   - **Business Account ID:** Your WhatsApp Business Account ID
4. **Enable:** Toggle the switch ON
5. **Save All Settings**

---

### **Step 7: Test Your Integration** (2 minutes)

1. **Click:** "Test WhatsApp Business API Connection"
2. **Enter:** Your WhatsApp number (with country code)
3. **Check your phone** - you should receive a message!

---

## 📝 Example Configuration

Here's what your settings should look like:

```
API Key: EAABsbCS1iHgBO7WfZBfqT8...  (long string)
API URL: https://graph.facebook.com/v18.0
Phone Number ID: 123456789012345
Business Account ID: 987654321098765
```

---

## ✅ Quick Test with cURL

Before configuring in CRM, test if your credentials work:

```bash
curl -X POST \
  https://graph.facebook.com/v18.0/YOUR_PHONE_NUMBER_ID/messages \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "to": "919876543210",
    "type": "template",
    "template": {
      "name": "hello_world",
      "language": {
        "code": "en_US"
      }
    }
  }'
```

**Replace:**
- `YOUR_PHONE_NUMBER_ID` with your Phone Number ID
- `YOUR_ACCESS_TOKEN` with your access token
- `919876543210` with your WhatsApp number

---

## 🎨 Create Your First Template

WhatsApp requires approved templates. Here's how:

### **Step 1: Go to Message Templates**

1. **In WhatsApp Dashboard:**
   - Click **"WhatsApp" → "Message Templates"**
2. **Click:** "Create Template"

### **Step 2: Create Simple Template**

**Template Name:** `lead_welcome`  
**Category:** Utility  
**Language:** English

**Example Content:**
```
Hello {{1}}!

Thank you for your interest in {{2}}.

We will contact you within 24 hours.

For questions, reply to this message.
```

**Variables:**
- `{{1}}` = Customer name
- `{{2}}` = Project name

### **Step 3: Submit for Approval**

1. **Review** your template
2. **Click:** "Submit"
3. **Wait** for approval (usually 5-30 minutes)

---

## 💰 Pricing Breakdown

### **Free Tier:**
- **1,000 conversations/month** - FREE
- **Conversation:** 24-hour window with a customer
- **Multiple messages** in 24 hours = 1 conversation

### **After Free Tier:**
- **Business-initiated:** ₹0.30-₹0.60 per conversation
- **User-initiated:** FREE (customer messages you first)

### **What's a Conversation?**
Example:
- Day 1, 10:00 AM: You send message → Conversation starts
- Day 1, 2:00 PM: Customer replies → Still same conversation
- Day 1, 5:00 PM: You send another message → Still same conversation
- Day 2, 11:00 AM: Conversation window closed (24 hours passed)
- Day 2, 3:00 PM: You send new message → NEW conversation (costs money)

**Tip:** Send all your messages within 24 hours to save money!

---

## 🔒 Security Best Practices

### **1. Protect Your Access Token:**
- Never commit to Git
- Store in environment variables
- Rotate regularly

### **2. Use Webhooks:**
Set up webhooks to receive messages (optional):
- Webhook URL: `https://yourdomain.com/Realvibe/api/webhooks/meta-incoming.php`
- Verify Token: Create a random string

### **3. Enable Two-Factor Authentication:**
On your Facebook account

---

## 🐛 Troubleshooting

### **Error: "Invalid OAuth access token"**
- Token expired → Generate new permanent token
- Wrong token → Double-check copy/paste

### **Error: "Phone number not found"**
- Wrong Phone Number ID → Check in dashboard
- Number not verified → Verify in WhatsApp Manager

### **Error: "Template not found"**
- Template not approved → Wait for approval
- Wrong template name → Check exact name in dashboard

### **Messages not sending:**
1. Check access token is valid
2. Verify phone number ID is correct
3. Check template is approved
4. Ensure recipient has WhatsApp

---

## 📊 Monitoring Usage

### **Check Message Count:**

1. **Go to:** WhatsApp Manager
2. **Click:** "Insights"
3. **View:** Conversations used this month

### **Set Up Billing Alerts:**

1. **Go to:** Business Settings → Payments
2. **Set spending limit** (e.g., $10/month)
3. **Enable email alerts**

---

## 🎯 Next Steps After Setup

Once configured:

1. ✅ **Test sending messages** from CRM
2. ✅ **Create more templates** for different use cases
3. ✅ **Set up webhooks** to receive replies
4. ✅ **Monitor usage** in Meta dashboard
5. ✅ **Get business verification** for unlimited contacts

---

## 📚 Useful Links

- **Meta Developer Docs:** https://developers.facebook.com/docs/whatsapp/cloud-api
- **WhatsApp Manager:** https://business.facebook.com/wa/manage
- **Message Templates:** https://business.facebook.com/wa/manage/message-templates
- **API Reference:** https://developers.facebook.com/docs/whatsapp/cloud-api/reference

---

## 🎓 Learning Resources

- **Quick Start Guide:** https://developers.facebook.com/docs/whatsapp/cloud-api/get-started
- **Send Messages Guide:** https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-messages
- **Template Messages:** https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates

---

## ✨ Pro Tips

1. **Use Template Buttons:** Add quick reply buttons to templates
2. **Track Conversations:** Monitor to stay under 1,000/month
3. **Batch Messages:** Send within 24-hour windows
4. **User-Initiated Free:** Let customers message you first when possible
5. **Pre-Approve Templates:** Create templates before you need them

---

**Ready to start?** Follow Step 1 and let me know when you've created your Facebook Business account! 🚀
