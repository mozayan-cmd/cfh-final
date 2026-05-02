#!/bin/bash

echo "========================================="
echo "CFH Fund Management - Setup Script"
echo "========================================="

# Navigate to project directory (adjust if needed)
cd "$(dirname "$0")"

echo ""
echo "Step 1: Adding cache drivers to .env..."
if ! grep -q "CACHE_DRIVER=array" .env 2>/dev/null; then
    echo "CACHE_DRIVER=array" >> .env
    echo "SESSION_DRIVER=array" >> .env
    echo "✓ Added cache drivers"
else
    echo "✓ Cache drivers already set"
fi

echo ""
echo "Step 2: Fixing permissions..."
chmod -R 777 storage bootstrap/cache 2>/dev/null
echo "✓ Permissions fixed"

echo ""
echo "Step 3: Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"

echo ""
echo "Step 4: Running fresh migration..."
php artisan migrate:fresh --force
echo "✓ Database migrated"

echo ""
echo "Step 5: Creating admin user..."
php artisan tinker --execute="
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_active' => true
]);
echo 'Admin user created!';
"
echo "✓ Admin user created"

echo ""
echo "========================================="
echo "Setup Complete!"
echo "========================================="
echo "Email: admin@example.com"
echo "Password: password"
echo ""
echo "Run: php artisan serve --host=0.0.0.0 --port=8000"
echo "========================================="