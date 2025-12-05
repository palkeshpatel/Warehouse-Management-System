# 🧹 Cache Clear Route - Quick Reference

## 📍 URL Route

**Route:** `/clear`

**Full URL:** `https://yuvaanenergylimited.com/clear?token=YOUR_SECRET_TOKEN`

---

## 🔐 Security Setup

### **Step 1: Set Token in .env File**

Add this line to your `.env` file:

```
CACHE_CLEAR_TOKEN=your-secret-token-here-12345
```

**⚠️ Important:** 
- Use a strong, random token
- Don't share this token publicly
- Change the default token if not set

### **Step 2: Access the Route**

Open your browser and go to:
```
https://yuvaanenergylimited.com/clear?token=your-secret-token-here-12345
```

Replace `your-secret-token-here-12345` with the token you set in `.env`.

---

## 🚀 What It Does

When you access the route, it automatically runs these commands:

### **Clearing Caches:**
1. `php artisan config:clear`
2. `php artisan cache:clear`
3. `php artisan route:clear`
4. `php artisan view:clear`
5. `php artisan optimize:clear`

### **Rebuilding Caches:**
6. `php artisan config:cache`
7. `php artisan route:cache`
8. `php artisan view:cache`

---

## 💻 Usage Examples

### **Via Browser:**
```
https://yuvaanenergylimited.com/clear?token=your-secret-token
```

### **Via cURL (Command Line):**
```bash
curl "https://yuvaanenergylimited.com/clear?token=your-secret-token"
```

### **Via PHP/JavaScript:**
```javascript
// JavaScript
fetch('https://yuvaanenergylimited.com/clear?token=your-secret-token')
  .then(response => response.text())
  .then(data => console.log(data));
```

---

## ✅ Expected Response

You'll see a success page with:
- ✅ All cache operations completed
- ✓ Each step marked as successful
- Links to go back to dashboard

---

## 🔒 Security Notes

1. **Token Protection:** The route is protected by a token. Without the correct token, access will be denied.

2. **Default Token:** If `CACHE_CLEAR_TOKEN` is not set in `.env`, the default token is `change-me-in-production`. **You must change this!**

3. **Best Practices:**
   - Use a long, random token (at least 32 characters)
   - Store token securely in `.env` file
   - Don't commit `.env` to version control
   - Rotate token periodically

---

## 🛠️ Troubleshooting

### **Issue: "Unauthorized. Invalid token"**

**Solution:**
1. Check if `CACHE_CLEAR_TOKEN` is set in `.env`
2. Ensure token in URL matches token in `.env`
3. Clear config cache: `php artisan config:clear` (if you just added it)

### **Issue: Page not loading**

**Solution:**
1. Check if route exists: `php artisan route:list | grep clear`
2. Clear route cache: `php artisan route:clear`
3. Try accessing route again

### **Issue: Commands not running**

**Solution:**
1. Check file permissions on `storage` and `bootstrap/cache` folders
2. Ensure PHP can execute shell commands
3. Check Laravel logs: `storage/logs/laravel.log`

---

## 📝 Example .env Configuration

```env
# Cache Clear Token (Change this to a secure random string)
CACHE_CLEAR_TOKEN=my-super-secret-token-1234567890-abcdefghijklmnop
```

---

## 🎯 Quick Setup for cPanel

1. **Add to .env file:**
   ```
   CACHE_CLEAR_TOKEN=my-secret-token-123
   ```

2. **Clear config cache** (via SSH or Terminal):
   ```bash
   php artisan config:clear
   ```

3. **Access the route:**
   ```
   https://yourdomain.com/clear?token=my-secret-token-123
   ```

That's it! You can now clear and rebuild all caches with a simple URL visit.

---

## ⚠️ Important Notes

- **Production Use:** This route is useful for cPanel deployments where SSH access might be limited
- **Security:** Always use a strong token in production
- **Performance:** This route may take a few seconds to complete
- **Backup:** Consider backing up before clearing caches if you have important cached data

---

**Need Help?** Check the main deployment documentation for more details.

