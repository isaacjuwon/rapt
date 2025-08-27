# Subdirectory Deployment Guide

## Completed Changes

✅ Created `.htaccess` file in `public/` directory for URL rewriting
✅ Updated `AppServiceProvider.php` to configure asset URLs for subdirectory deployment
✅ Updated `vite.config.js` to handle dynamic base path

## Remaining Steps to Complete

### 1. Update Your .env File

Update your `.env` file with the correct APP_URL for your subdirectory:

```env
# Replace with your actual domain and subdirectory path
APP_URL=https://test.yourdomain.com/test
# or if it's a subdirectory: https://yourdomain.com/test

# Make sure these are set for production
APP_ENV=production
APP_DEBUG=false

# Session configuration for subdomain
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### 2. Build Production Assets

Run the following commands to build assets for production:

```bash
npm run build
```

### 3. Clear Laravel Caches

Clear all Laravel caches after configuration changes:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### 4. Upload Files to Server

Upload your entire project to your subdirectory on the server. Make sure:

- The `public` folder contents go in your `/test/` subdirectory
- The rest of the Laravel application goes outside the web root
- Set proper permissions (755 for directories, 644 for files)
- Make sure `storage` and `bootstrap/cache` directories are writable

### 5. Web Server Configuration

#### Apache (.htaccess is already created)
Ensure your Apache virtual host allows .htaccess overrides:

```apache
<Directory "/path/to/your/subdomain/test">
    AllowOverride All
    Require all granted
</Directory>
```

#### Nginx (if using Nginx instead)
Add this to your server configuration:

```nginx
location /test {
    alias /path/to/your/app/public;
    try_files $uri $uri/ @test;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}

location @test {
    rewrite /test/(.*)$ /test/index.php?/$1 last;
}
```

### 6. Test the Deployment

1. Access your application at `https://test.yourdomain.com/test`
2. Check browser console for any 404 errors on Livewire assets
3. Test Livewire components to ensure they work properly
4. Verify that forms and AJAX requests are working

## Troubleshooting

### Common Issues:

1. **Assets not loading**: Check that `APP_URL` in `.env` matches your actual URL
2. **Livewire not working**: Ensure the asset URLs are correct and accessible
3. **CSRF token errors**: Check session configuration and domain settings
4. **404 on routes**: Verify .htaccess is working and mod_rewrite is enabled

### Debug Steps:

1. Check browser developer tools Network tab for failed requests
2. Check server error logs for detailed error messages
3. Verify file permissions are correct
4. Test with `APP_DEBUG=true` temporarily to see detailed errors

## Files Modified:

- `public/.htaccess` (created)
- `app/Providers/AppServiceProvider.php` (updated)
- `vite.config.js` (updated)

## Next Steps:

1. Update your `.env` file with production settings
2. Run `npm run build` to build production assets
3. Clear Laravel caches
4. Upload to your server
5. Test the deployment# Subdirectory Deployment Guide

## Completed Changes

✅ Created `.htaccess` file in `public/` directory for URL rewriting
✅ Updated `AppServiceProvider.php` to configure asset URLs for subdirectory deployment
✅ Updated `vite.config.js` to handle dynamic base path

## Remaining Steps to Complete

### 1. Update Your .env File

Update your `.env` file with the correct APP_URL for your subdirectory:

```env
# Replace with your actual domain and subdirectory path
APP_URL=https://test.yourdomain.com/test
# or if it's a subdirectory: https://yourdomain.com/test

# Make sure these are set for production
APP_ENV=production
APP_DEBUG=false

# Session configuration for subdomain
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### 2. Build Production Assets

Run the following commands to build assets for production:

```bash
npm run build
```

### 3. Clear Laravel Caches

Clear all Laravel caches after configuration changes:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### 4. Upload Files to Server

Upload your entire project to your subdirectory on the server. Make sure:

- The `public` folder contents go in your `/test/` subdirectory
- The rest of the Laravel application goes outside the web root
- Set proper permissions (755 for directories, 644 for files)
- Make sure `storage` and `bootstrap/cache` directories are writable

### 5. Web Server Configuration

#### Apache (.htaccess is already created)
Ensure your Apache virtual host allows .htaccess overrides:

```apache
<Directory "/path/to/your/subdomain/test">
    AllowOverride All
    Require all granted
</Directory>
```

#### Nginx (if using Nginx instead)
Add this to your server configuration:

```nginx
location /test {
    alias /path/to/your/app/public;
    try_files $uri $uri/ @test;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}

location @test {
    rewrite /test/(.*)$ /test/index.php?/$1 last;
}
```

### 6. Test the Deployment

1. Access your application at `https://test.yourdomain.com/test`
2. Check browser console for any 404 errors on Livewire assets
3. Test Livewire components to ensure they work properly
4. Verify that forms and AJAX requests are working

## Troubleshooting

### Common Issues:

1. **Assets not loading**: Check that `APP_URL` in `.env` matches your actual URL
2. **Livewire not working**: Ensure the asset URLs are correct and accessible
3. **CSRF token errors**: Check session configuration and domain settings
4. **404 on routes**: Verify .htaccess is working and mod_rewrite is enabled

### Debug Steps:

1. Check browser developer tools Network tab for failed requests
2. Check server error logs for detailed error messages
3. Verify file permissions are correct
4. Test with `APP_DEBUG=true` temporarily to see detailed errors

## Files Modified:

- `public/.htaccess` (created)
- `app/Providers/AppServiceProvider.php` (updated)
- `vite.config.js` (updated)

## Next Steps:

1. Update your `.env` file with production settings
2. Run `npm run build` to build production assets
3. Clear Laravel caches
4. Upload to your server
5. Test the deployment