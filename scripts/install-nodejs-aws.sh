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
echo "Installing Puppeteer (for Browsershot)..."
echo "=========================================="
echo ""

# Create a temporary directory for npm packages
TEMP_DIR="/tmp/puppeteer-install"
mkdir -p $TEMP_DIR
cd $TEMP_DIR

# Install Puppeteer globally
npm install -g puppeteer

# Change ownership to www-data for the npm global directory
echo "Setting permissions for www-data user..."
chown -R www-data:www-data /usr/lib/node_modules 2>/dev/null || true

# Verify Puppeteer installation
echo ""
echo "Puppeteer installed at:"
npm list -g puppeteer 2>/dev/null || echo "Puppeteer installed globally"

# Clean up
cd /
rm -rf $TEMP_DIR

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
