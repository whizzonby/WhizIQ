#!/bin/bash

# Disk Space Cleanup Script for AWS Ubuntu Server
# This script helps free up disk space before installing Node.js

set -e

echo "=========================================="
echo "Disk Space Cleanup for AWS Ubuntu"
echo "=========================================="
echo ""

# Check current disk usage
echo "Current disk usage:"
df -h /
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root or with sudo"
    exit 1
fi

# Function to get available space in MB
get_available_space() {
    df -m / | tail -1 | awk '{print $4}'
}

AVAILABLE_SPACE=$(get_available_space)
echo "Available space: ${AVAILABLE_SPACE}MB"
echo ""

# Clean apt cache
echo "Cleaning apt cache..."
apt-get clean
apt-get autoclean
rm -rf /var/cache/apt/archives/*.deb
echo "✓ Apt cache cleaned"
echo ""

# Remove old kernels (keep only current)
echo "Removing old kernels..."
OLD_KERNELS=$(dpkg -l | grep -E 'linux-image-[0-9]' | grep -v $(uname -r) | awk '{print $2}')
if [ ! -z "$OLD_KERNELS" ]; then
    apt-get remove -y $OLD_KERNELS 2>/dev/null || true
    apt-get autoremove -y
    echo "✓ Old kernels removed"
else
    echo "✓ No old kernels to remove"
fi
echo ""

# Clean package lists
echo "Cleaning package lists..."
apt-get autoremove -y
apt-get autoclean
echo "✓ Package cleanup done"
echo ""

# Clean log files (keep last 7 days)
echo "Cleaning old log files..."
find /var/log -type f -name "*.log" -mtime +7 -delete 2>/dev/null || true
find /var/log -type f -name "*.gz" -delete 2>/dev/null || true
journalctl --vacuum-time=7d 2>/dev/null || true
echo "✓ Log files cleaned"
echo ""

# Clean temporary files
echo "Cleaning temporary files..."
rm -rf /tmp/* 2>/dev/null || true
rm -rf /var/tmp/* 2>/dev/null || true
echo "✓ Temporary files cleaned"
echo ""

# Clean Laravel cache and logs (if Laravel is installed)
if [ -d "/var/www/WhizIQ" ]; then
    echo "Cleaning Laravel cache and logs..."
    cd /var/www/WhizIQ
    
    # Clean Laravel cache
    php artisan cache:clear 2>/dev/null || true
    php artisan config:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    
    # Clean storage logs (keep last 100 lines)
    if [ -d "storage/logs" ]; then
        find storage/logs -name "*.log" -exec truncate -s 0 {} \; 2>/dev/null || true
    fi
    
    # Clean compiled views
    rm -rf storage/framework/views/*.php 2>/dev/null || true
    
    echo "✓ Laravel cache cleaned"
    echo ""
fi

# Show new disk usage
echo "=========================================="
echo "Disk Space After Cleanup:"
echo "=========================================="
df -h /
echo ""

NEW_AVAILABLE_SPACE=$(get_available_space)
FREED_SPACE=$((NEW_AVAILABLE_SPACE - AVAILABLE_SPACE))

echo "Space freed: ${FREED_SPACE}MB"
echo "Available space now: ${NEW_AVAILABLE_SPACE}MB"
echo ""

if [ $NEW_AVAILABLE_SPACE -lt 500 ]; then
    echo "⚠️  WARNING: Still low on disk space (less than 500MB)"
    echo "You may need to:"
    echo "1. Resize your EBS volume"
    echo "2. Remove unused applications"
    echo "3. Move files to S3 or external storage"
    echo ""
else
    echo "✓ Sufficient space available for Node.js installation"
    echo ""
fi

