#!/bin/bash

# Quick Disk Space Check Script

echo "=========================================="
echo "Disk Space Check"
echo "=========================================="
echo ""

# Show disk usage
echo "Disk Usage:"
df -h
echo ""

# Show inode usage
echo "Inode Usage:"
df -i
echo ""

# Show largest directories
echo "Top 10 Largest Directories in /:"
du -h --max-depth=1 / 2>/dev/null | sort -rh | head -10
echo ""

# Show largest files
echo "Top 10 Largest Files:"
find / -type f -size +100M 2>/dev/null | head -10 | xargs ls -lh 2>/dev/null || echo "No files larger than 100MB found"
echo ""

# Check specific common space hogs
echo "Common Space Hogs:"
echo "  /var/log: $(du -sh /var/log 2>/dev/null | cut -f1)"
echo "  /var/cache: $(du -sh /var/cache 2>/dev/null | cut -f1)"
echo "  /tmp: $(du -sh /tmp 2>/dev/null | cut -f1)"
echo "  /var/tmp: $(du -sh /var/tmp 2>/dev/null | cut -f1)"
if [ -d "/var/www/WhizIQ" ]; then
    echo "  /var/www/WhizIQ: $(du -sh /var/www/WhizIQ 2>/dev/null | cut -f1)"
    echo "    storage: $(du -sh /var/www/WhizIQ/storage 2>/dev/null | cut -f1)"
    echo "    vendor: $(du -sh /var/www/WhizIQ/vendor 2>/dev/null | cut -f1)"
    echo "    node_modules: $(du -sh /var/www/WhizIQ/node_modules 2>/dev/null | cut -f1 || echo 'N/A')"
fi
echo ""

