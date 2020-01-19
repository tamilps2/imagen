#!/bin/bash
set -e

cp -p .env.example .env

echo "Checking composer packages..."
echo ""

VENDOR="vendor"
if [ ! -d "$VENDOR" ]; then
  echo "Installing composer packages..."
  composer install
fi

echo "Checking node packages..."
echo ""

NODE="node_modules"
if [ ! -d "$NODE" ]; then
  echo "Installing npm packages..."
  npm install && npm run prod
fi

# Create database file
touch database/database.sqlite

# Modify this permission if sqlite is used in production
echo "NOTE: Setting full permission for sqlite database. Modify permission if using in production."
echo ""
chmod 777 database/database.sqlite

# Make framework folders
echo "Creating required framework directories..."
echo ""
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache

echo "Setting up framework..."
echo ""
php artisan key:generate
php artisan migrate:fresh

echo "Setting up application directories..."
echo ""
# Make the required directories
mkdir -p storage/app/jobs
mkdir -p storage/app/jobs/uploads
mkdir -p storage/app/jobs/output
mkdir -p storage/app/logos

# Link for company logos
echo ""
CWD=$(pwd)
ln -sf "$CWD"/storage/app/logos "$CWD"/public/logos

# Check if ffmpeg is installed
echo ""
echo "Checking if ffmpeg is insatlled..."

dpkg-query -l ffmpeg &> /dev/null

if [ $? -eq 0 ]; then
  echo "ffmpeg is installed"
else
  echo ""
  echo "WARNING: ffmpeg is not installed. ffmpeg is required to create videos. Run 'sudo apt install ffmpeg'"
  echo ""
fi

echo ""
echo "#---- Install finished ----#"
echo "The app is using sqlite database as default. You can modify the .env file to use a different database."
echo ""
echo "Take a look at the config/imager.php to modify the imager settings."
echo "The FTP and Youtube uploads are implemented with laravel queues. You need to run the queues to process them."
echo ""
echo "To start a dev server - 'php artisan serv'"
echo "To start queue workder - 'php artisan queue:work --timeout=1500'"
echo ""
