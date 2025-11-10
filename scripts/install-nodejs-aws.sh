#!/bin/bash

# Install Node.js and npm on AWS Ubuntu Server (system-wide)
# This script installs Node.js 20.x LTS from the NodeSource repository
# Run as root or with sudo

set -e

echo "=========================================="
echo "Installing Node.js 20.x LTS on AWS Ubuntu"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root or with sudo"
    exit 1
fi

# Check disk space before proceeding
echo "Checking disk space..."
AVAILABLE_SPACE=$(df -m / | tail -1 | awk '{print $4}')
echo "Available disk space: ${AVAILABLE_SPACE}MB"
echo ""

if [ $AVAILABLE_SPACE -lt 500 ]; then
    echo "⚠️  WARNING: Low disk space (${AVAILABLE_SPACE}MB available)"
    echo "Node.js installation requires at least 200MB free space."
    echo ""
    echo "Please run the cleanup script first:"
    echo "  sudo ./scripts/cleanup-disk-space.sh"
    echo ""
    echo "Or manually free up space, then run this script again."
    exit 1
fi

echo "✓ Sufficient disk space available"
echo ""

# Update package list
echo "Updating package list..."
apt-get update

# Install prerequisites
echo "Installing prerequisites..."
apt-get install -y ca-certificates curl gnupg

# Create directory for keyrings
mkdir -p /etc/apt/keyrings

# Download and install NodeSource GPG key
echo "Adding NodeSource repository..."
curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

# Setup NodeSource repository for Node.js 20.x
NODE_MAJOR=20
echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

# Update package list again
echo "Updating package list with NodeSource repository..."
apt-get update

# Install Node.js and npm
echo "Installing Node.js and npm..."
apt-get install -y nodejs

# Verify installation
echo ""
echo "=========================================="
echo "Installation Complete!"
echo "=========================================="
echo ""
echo "Node.js version:"
node --version
echo ""
echo "npm version:"
npm --version
echo ""
echo "Node.js binary location:"
which node
echo ""
echo "npm binary location:"
which npm
echo ""

# Install Puppeteer dependencies (required by Browsershot)
echo "Installing Puppeteer dependencies..."
apt-get install -y \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxkbcommon0 \
    libxcomposite1 \
    libxdamage1 \
    libxfixes3 \
    libxrandr2 \
    libgbm1 \
    libasound2

echo ""
echo "=========================================="
echo "Installing Puppeteer and Chrome..."
echo "=========================================="
echo ""

# Navigate to project directory
PROJECT_DIR="/var/www/WhizIQ"
if [ ! -d "$PROJECT_DIR" ]; then
    echo "⚠️  Project directory not found at $PROJECT_DIR"
    echo "Please install Puppeteer manually in your project directory"
    exit 1
fi

cd $PROJECT_DIR

# Install Puppeteer locally in the project (this downloads Chrome)
echo "Installing Puppeteer in project (this will download Chrome)..."
npm install puppeteer --save-dev --legacy-peer-deps 2>/dev/null || npm install puppeteer --save-dev

# Install Chrome using Puppeteer's browser installer
echo "Installing Chrome via Puppeteer..."
npx puppeteer browsers install chrome 2>/dev/null || npx --yes puppeteer browsers install chrome

# Set permissions for www-data user
echo "Setting permissions for www-data user..."
chown -R www-data:www-data node_modules 2>/dev/null || true
chown -R www-data:www-data .cache 2>/dev/null || true

# Find Chrome path
CHROME_PATH=$(find node_modules -name "chrome" -type f -path "*/chrome-linux*/chrome" 2>/dev/null | head -1)
if [ -z "$CHROME_PATH" ]; then
    CHROME_PATH=$(find ~/.cache -name "chrome" -type f -path "*/chrome-linux*/chrome" 2>/dev/null | head -1)
fi
if [ -z "$CHROME_PATH" ]; then
    CHROME_PATH=$(find /var/www/.cache -name "chrome" -type f -path "*/chrome-linux*/chrome" 2>/dev/null | head -1)
fi

if [ ! -z "$CHROME_PATH" ]; then
    CHROME_PATH=$(readlink -f "$CHROME_PATH" 2>/dev/null || echo "$CHROME_PATH")
    echo ""
    echo "✓ Chrome found at: $CHROME_PATH"
    echo ""
    echo "Add this to your .env file:"
    echo "CHROME_PATH=$CHROME_PATH"
else
    echo ""
    echo "⚠️  Chrome path not automatically detected"
    echo "You may need to set CHROME_PATH in your .env file manually"
fi

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Node.js and npm are now available system-wide at:"
echo "  - $(which node)"
echo "  - $(which npm)"
echo ""
echo "These paths are accessible by all users including www-data"
echo ""
echo "You can now test invoice PDF generation in WhizIQ"
echo ""
