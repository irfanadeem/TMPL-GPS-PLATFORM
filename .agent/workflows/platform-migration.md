---
description: Complete Platform Migration Guide
---

# Complete Platform Migration Guide

This guide provides a comprehensive migration plan for the GPS Tracking Platform (GPSWox) with all related services.

## Current Platform Architecture

### Services Overview
1. **Web Application** - Laravel-based GPS tracking platform
2. **Database** - MySQL (2 databases: `gpswox_web`, `gpswox_traccar`)
3. **Traccar Server** - GPS device listener (ports 6000-8024)
4. **Redis** - Real-time data caching and pub/sub
5. **WebSocket Server** - Node.js socket.io server (ports 9001/9002)
6. **Apache/HTTPD** - Web server with PHP-FPM
7. **Node.js** - v20.19.5 for WebSocket server

### Current Configuration
- **Domain**: jazz.telematicsmaster.com
- **PHP Version**: 8.3.30
- **Laravel Framework**: Custom GPSWox implementation
- **Traccar Location**: /opt/traccar
- **Web Root**: /var/www/html
- **SSL Certificates**: Located in /var/www/html (private.key, cert.crt, cert_ca.crt)

---

## Migration Plan

### Phase 1: Pre-Migration Assessment & Backup

#### 1.1 Create Complete Backup
```bash
# Create backup directory with timestamp
BACKUP_DIR="/root/migration_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR

# Backup databases
mysqldump -u wox -p gpswox_web > $BACKUP_DIR/gpswox_web.sql
mysqldump -u wox -p gpswox_traccar > $BACKUP_DIR/gpswox_traccar.sql

# Backup web application
tar -czf $BACKUP_DIR/html_backup.tar.gz /var/www/html \
  --exclude='/var/www/html/vendor' \
  --exclude='/var/www/html/node_modules' \
  --exclude='/var/www/html/storage/logs'

# Backup Traccar
tar -czf $BACKUP_DIR/traccar_backup.tar.gz /opt/traccar

# Backup Redis data (if persistence enabled)
cp /var/lib/redis/dump.rdb $BACKUP_DIR/redis_dump.rdb 2>/dev/null || echo "No Redis dump found"

# Backup system configurations
cp /etc/httpd/conf/httpd.conf $BACKUP_DIR/httpd.conf
cp /etc/httpd/conf.d/*.conf $BACKUP_DIR/
cp /etc/redis.conf $BACKUP_DIR/redis.conf 2>/dev/null || cp /etc/redis/redis.conf $BACKUP_DIR/redis.conf

# Create inventory file
cat > $BACKUP_DIR/INVENTORY.txt << EOF
Backup Created: $(date)
Source Server: $(hostname)
IP Address: $(hostname -I | awk '{print $1}')

Services:
- Web Application: /var/www/html
- Traccar: /opt/traccar
- Database (web): gpswox_web
- Database (traccar): gpswox_traccar
- Redis: Running on 127.0.0.1:6379
- WebSocket: Running on ports 9001/9002
- PHP Version: $(php --version | head -1)
- Node Version: $(node --version)

Database Sizes:
$(mysql -u wox -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema IN ('gpswox_web', 'gpswox_traccar') GROUP BY table_schema;")
EOF

echo "Backup created at: $BACKUP_DIR"
```

#### 1.2 Document Current State
```bash
# Service status
systemctl status httpd --no-pager > $BACKUP_DIR/service_httpd.txt
systemctl status redis --no-pager > $BACKUP_DIR/service_redis.txt
systemctl status traccar --no-pager > $BACKUP_DIR/service_traccar.txt

# Port usage
netstat -tulpn | grep -E "(3306|6379|8082|9001|9002|6000|6001|6002|6003|6004)" > $BACKUP_DIR/ports.txt

# Cron jobs
crontab -l > $BACKUP_DIR/crontab_backup.txt 2>/dev/null || echo "No crontab" > $BACKUP_DIR/crontab_backup.txt

# Environment variables
cp /var/www/html/.env $BACKUP_DIR/env_backup.txt
```

---

### Phase 2: Prepare Target Server

#### 2.1 Install Required Software

```bash
# Update system
sudo yum update -y  # For CentOS/RHEL
# OR
sudo apt update && sudo apt upgrade -y  # For Ubuntu/Debian

# Install Apache/Nginx
sudo yum install httpd -y  # CentOS/RHEL
# OR
sudo apt install apache2 -y  # Ubuntu/Debian

# Install PHP 8.3
sudo yum install php83 php83-cli php83-fpm php83-mysqlnd php83-pdo php83-mbstring \
  php83-xml php83-gd php83-curl php83-zip php83-opcache php83-json php83-redis -y

# Install MySQL/MariaDB
sudo yum install mariadb-server mariadb -y
# OR
sudo apt install mysql-server -y

# Install Redis
sudo yum install redis -y
# OR
sudo apt install redis-server -y

# Install Node.js 20.x
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo yum install nodejs -y
# OR for Ubuntu/Debian
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Java for Traccar
sudo yum install java-11-openjdk -y
# OR
sudo apt install openjdk-11-jdk -y
```

#### 2.2 Configure Firewall
```bash
# Open required ports
sudo firewall-cmd --permanent --add-port=80/tcp
sudo firewall-cmd --permanent --add-port=443/tcp
sudo firewall-cmd --permanent --add-port=8082/tcp  # Traccar web
sudo firewall-cmd --permanent --add-port=9001/tcp  # WebSocket HTTP
sudo firewall-cmd --permanent --add-port=9002/tcp  # WebSocket HTTPS
sudo firewall-cmd --permanent --add-port=6000-6250/tcp  # Traccar GPS ports
sudo firewall-cmd --permanent --add-port=6300-6303/tcp
sudo firewall-cmd --permanent --add-port=6600/tcp
sudo firewall-cmd --permanent --add-port=7013-7028/tcp
sudo firewall-cmd --permanent --add-port=7056/tcp
sudo firewall-cmd --permanent --add-port=8023-8024/tcp
sudo firewall-cmd --reload

# OR for UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8082/tcp
sudo ufw allow 9001/tcp
sudo ufw allow 9002/tcp
sudo ufw allow 6000:6250/tcp
sudo ufw allow 6300:6303/tcp
sudo ufw allow 6600/tcp
sudo ufw allow 7013:7028/tcp
sudo ufw allow 7056/tcp
sudo ufw allow 8023:8024/tcp
```

---

### Phase 3: Database Migration

#### 3.1 Transfer Database Dumps
```bash
# On source server, compress databases
cd $BACKUP_DIR
gzip gpswox_web.sql
gzip gpswox_traccar.sql

# Transfer to target server (replace TARGET_IP with actual IP)
scp gpswox_web.sql.gz root@TARGET_IP:/tmp/
scp gpswox_traccar.sql.gz root@TARGET_IP:/tmp/
```

#### 3.2 Restore Databases on Target Server
```bash
# On target server
cd /tmp
gunzip gpswox_web.sql.gz
gunzip gpswox_traccar.sql.gz

# Create databases
mysql -u root -p << EOF
CREATE DATABASE gpswox_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE gpswox_traccar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'wox'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON gpswox_web.* TO 'wox'@'localhost';
GRANT ALL PRIVILEGES ON gpswox_traccar.* TO 'wox'@'localhost';

CREATE USER 'sa'@'localhost' IDENTIFIED BY 'YOUR_TRACCAR_PASSWORD';
GRANT ALL PRIVILEGES ON gpswox_traccar.* TO 'sa'@'localhost';

FLUSH PRIVILEGES;
EOF

# Import databases
mysql -u root -p gpswox_web < /tmp/gpswox_web.sql
mysql -u root -p gpswox_traccar < /tmp/gpswox_traccar.sql

# Verify import
mysql -u root -p -e "USE gpswox_web; SHOW TABLES; SELECT COUNT(*) FROM devices;"
mysql -u root -p -e "USE gpswox_traccar; SHOW TABLES;"
```

---

### Phase 4: Application Migration

#### 4.1 Transfer Application Files
```bash
# On source server
cd /var/www
tar -czf html_full.tar.gz html

# Transfer to target
scp html_full.tar.gz root@TARGET_IP:/var/www/

# On target server
cd /var/www
tar -xzf html_full.tar.gz
chown -R apache:apache html  # CentOS/RHEL
# OR
chown -R www-data:www-data html  # Ubuntu/Debian
```

#### 4.2 Configure Application
```bash
cd /var/www/html

# Update .env file with new database credentials
nano .env
# Update these lines:
# DB_PASSWORD=YOUR_SECURE_PASSWORD
# web_password=YOUR_SECURE_PASSWORD
# traccar_password=YOUR_SECURE_PASSWORD

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 public/uploads

# Generate application key (if needed)
php artisan key:generate

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if any pending)
php artisan migrate --force
```

#### 4.3 Install Node.js Dependencies
```bash
cd /var/www/html/socket
npm install

# Test socket server
node socket.js
# Press Ctrl+C after verifying it starts without errors
```

---

### Phase 5: Traccar Migration

#### 5.1 Transfer Traccar
```bash
# On source server
cd /opt
tar -czf traccar.tar.gz traccar

# Transfer to target
scp traccar.tar.gz root@TARGET_IP:/opt/

# On target server
cd /opt
tar -xzf traccar.tar.gz
```

#### 5.2 Configure Traccar
```bash
# Update traccar.xml with new database password
nano /opt/traccar/conf/traccar.xml
# Update line 25: <entry key="database.password">YOUR_TRACCAR_PASSWORD</entry>

# Create Traccar service
cat > /etc/systemd/system/traccar.service << 'EOF'
[Unit]
Description=Traccar GPS Tracking Server
After=network.target mysql.service

[Service]
Type=forking
User=root
WorkingDirectory=/opt/traccar
ExecStart=/opt/traccar/bin/traccar start
ExecStop=/opt/traccar/bin/traccar stop
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# Enable and start Traccar
systemctl daemon-reload
systemctl enable traccar
systemctl start traccar
systemctl status traccar
```

---

### Phase 6: Redis Configuration

#### 6.1 Configure Redis
```bash
# Edit Redis configuration
nano /etc/redis.conf  # or /etc/redis/redis.conf

# Ensure these settings:
# bind 127.0.0.1
# port 6379
# maxmemory 256mb
# maxmemory-policy allkeys-lru

# Enable and start Redis
systemctl enable redis
systemctl start redis
systemctl status redis

# Test Redis
redis-cli ping
# Should return: PONG
```

---

### Phase 7: WebSocket Server Setup

#### 7.1 Create WebSocket Service
```bash
# Create systemd service for socket server
cat > /etc/systemd/system/gpswox-socket.service << 'EOF'
[Unit]
Description=GPSWox WebSocket Server
After=network.target redis.service

[Service]
Type=simple
User=root
WorkingDirectory=/var/www/html/socket
ExecStart=/usr/bin/node socket.js
Restart=on-failure
RestartSec=10
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=gpswox-socket

[Install]
WantedBy=multi-user.target
EOF

# Enable and start service
systemctl daemon-reload
systemctl enable gpswox-socket
systemctl start gpswox-socket
systemctl status gpswox-socket
```

---

### Phase 8: Web Server Configuration

#### 8.1 Configure Apache Virtual Host
```bash
# Create virtual host configuration
cat > /etc/httpd/conf.d/gpswox.conf << 'EOF'
<VirtualHost *:80>
    ServerName jazz.telematicsmaster.com
    ServerAlias www.jazz.telematicsmaster.com
    
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog /var/log/httpd/gpswox_error.log
    CustomLog /var/log/httpd/gpswox_access.log combined
    
    # PHP-FPM Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php-fpm/www.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>

<VirtualHost *:443>
    ServerName jazz.telematicsmaster.com
    ServerAlias www.jazz.telematicsmaster.com
    
    DocumentRoot /var/www/html/public
    
    SSLEngine on
    SSLCertificateFile /var/www/html/cert.crt
    SSLCertificateKeyFile /var/www/html/private.key
    SSLCertificateChainFile /var/www/html/cert_ca.crt
    
    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog /var/log/httpd/gpswox_ssl_error.log
    CustomLog /var/log/httpd/gpswox_ssl_access.log combined
    
    # PHP-FPM Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php-fpm/www.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
EOF

# Enable required modules
sudo yum install mod_ssl -y

# Test configuration
httpd -t

# Restart Apache
systemctl restart httpd
```

#### 8.2 Configure PHP-FPM
```bash
# Edit PHP-FPM pool configuration
nano /etc/php-fpm.d/www.conf

# Ensure these settings:
# user = apache (or www-data for Ubuntu)
# group = apache (or www-data for Ubuntu)
# listen = /run/php-fpm/www.sock
# listen.owner = apache
# listen.group = apache
# pm = dynamic
# pm.max_children = 50
# pm.start_servers = 5
# pm.min_spare_servers = 5
# pm.max_spare_servers = 35

# Enable and start PHP-FPM
systemctl enable php-fpm
systemctl start php-fpm
systemctl status php-fpm
```

---

### Phase 9: SSL Certificates Migration

#### 9.1 Transfer SSL Certificates
```bash
# On source server
cd /var/www/html
tar -czf ssl_certs.tar.gz private.key cert.crt cert_ca.crt

# Transfer to target
scp ssl_certs.tar.gz root@TARGET_IP:/var/www/html/

# On target server
cd /var/www/html
tar -xzf ssl_certs.tar.gz
chmod 600 private.key
chmod 644 cert.crt cert_ca.crt
```

---

### Phase 10: DNS & Network Configuration

#### 10.1 Update DNS Records
```
Before switching:
1. Lower TTL on DNS records to 300 seconds (5 minutes)
2. Wait for old TTL to expire (usually 24 hours)

During migration:
3. Update A record for jazz.telematicsmaster.com to new server IP
4. Wait 5-10 minutes for DNS propagation

After migration:
5. Increase TTL back to 3600 seconds (1 hour)
```

#### 10.2 Update Application URLs
```bash
# Update .env file
nano /var/www/html/.env
# Update APP_URL to new server IP or domain

# Update Traccar configuration
nano /opt/traccar/conf/traccar.xml
# Update web.url to new server IP or domain

# Clear cache
cd /var/www/html
php artisan config:clear
php artisan cache:clear
```

---

### Phase 11: Testing & Validation

#### 11.1 Service Health Checks
```bash
# Check all services
systemctl status httpd
systemctl status php-fpm
systemctl status mysql
systemctl status redis
systemctl status traccar
systemctl status gpswox-socket

# Check ports
netstat -tulpn | grep -E "(80|443|3306|6379|8082|9001|9002)"

# Check logs
tail -f /var/log/httpd/gpswox_error.log
tail -f /opt/traccar/logs/tracker-server.log
journalctl -u gpswox-socket -f
```

#### 11.2 Application Testing
```bash
# Test database connectivity
cd /var/www/html
php artisan tinker
# Run: DB::connection()->getPdo();
# Should not throw errors

# Test Redis connectivity
redis-cli
# Run: PING
# Should return: PONG

# Test WebSocket
curl http://localhost:9001
# Should return socket.io response

# Test Traccar
curl http://localhost:8082
# Should return Traccar web interface
```

#### 11.3 Functional Testing
```
1. Access web application: https://jazz.telematicsmaster.com
2. Login with admin credentials
3. Check dashboard loads correctly
4. Verify devices are visible
5. Check real-time updates are working
6. Test GPS device connection to Traccar
7. Verify position updates in database
8. Test WebSocket real-time updates
9. Check reports generation
10. Test mobile app connectivity
```

---

### Phase 12: Post-Migration Tasks

#### 12.1 Performance Optimization
```bash
# Enable OPcache
nano /etc/php.ini
# Ensure:
# opcache.enable=1
# opcache.memory_consumption=128
# opcache.max_accelerated_files=10000
# opcache.revalidate_freq=2

# Restart PHP-FPM
systemctl restart php-fpm

# Optimize MySQL
mysql_secure_installation

# Configure MySQL for performance
nano /etc/my.cnf
# Add under [mysqld]:
# innodb_buffer_pool_size = 1G
# max_connections = 200
# query_cache_size = 32M

systemctl restart mysqld
```

#### 12.2 Setup Monitoring
```bash
# Install monitoring tools
yum install htop iotop nethogs -y

# Setup log rotation
cat > /etc/logrotate.d/gpswox << 'EOF'
/var/www/html/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 apache apache
    sharedscripts
}
EOF

# Setup cron jobs (if any from old server)
crontab -e
# Add any scheduled tasks from backup
```

#### 12.3 Security Hardening
```bash
# Disable directory listing
echo "Options -Indexes" > /var/www/html/public/.htaccess

# Set proper file permissions
find /var/www/html -type f -exec chmod 644 {} \;
find /var/www/html -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Configure SELinux (if enabled)
setenforce 0  # Temporarily disable
# OR configure properly:
setsebool -P httpd_can_network_connect 1
setsebool -P httpd_can_network_connect_db 1
chcon -R -t httpd_sys_rw_content_t /var/www/html/storage
```

---

### Phase 13: Rollback Plan

#### 13.1 If Migration Fails
```bash
# Revert DNS to old server
# Update A record back to old IP

# On old server, ensure all services are running:
systemctl start httpd
systemctl start mysql
systemctl start redis
systemctl start traccar

# Restore from backup if needed:
cd $BACKUP_DIR
mysql -u root -p gpswox_web < gpswox_web.sql
mysql -u root -p gpswox_traccar < gpswox_traccar.sql
```

---

### Phase 14: Decommission Old Server

#### 14.1 After Successful Migration (Wait 1-2 weeks)
```bash
# On old server:
# 1. Stop all services
systemctl stop httpd
systemctl stop mysql
systemctl stop redis
systemctl stop traccar

# 2. Keep backups for 30 days
# 3. Monitor new server for stability
# 4. After 30 days, decommission old server
```

---

## Migration Checklist

### Pre-Migration
- [ ] Create complete backup of all services
- [ ] Document current configuration
- [ ] Test backup restoration
- [ ] Prepare target server
- [ ] Install all required software
- [ ] Configure firewall rules

### Migration
- [ ] Transfer and restore databases
- [ ] Transfer application files
- [ ] Transfer Traccar installation
- [ ] Transfer SSL certificates
- [ ] Configure all services
- [ ] Update configuration files
- [ ] Update DNS records

### Post-Migration
- [ ] Test all services
- [ ] Verify database connectivity
- [ ] Test GPS device connections
- [ ] Verify real-time updates
- [ ] Test WebSocket functionality
- [ ] Monitor logs for errors
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Setup monitoring
- [ ] Document new configuration

### Validation
- [ ] Web application accessible
- [ ] User login working
- [ ] Devices visible and updating
- [ ] Real-time tracking functional
- [ ] Reports generating correctly
- [ ] Mobile app connectivity
- [ ] WebSocket real-time updates
- [ ] Traccar receiving GPS data
- [ ] Redis caching working
- [ ] All API endpoints functional

---

## Estimated Timeline

- **Phase 1-2**: 2-4 hours (Backup & Preparation)
- **Phase 3-4**: 2-3 hours (Database & Application)
- **Phase 5-8**: 2-3 hours (Services Configuration)
- **Phase 9-10**: 1-2 hours (SSL & DNS)
- **Phase 11**: 2-4 hours (Testing)
- **Phase 12-14**: 1-2 hours (Optimization & Cleanup)

**Total Estimated Time**: 10-18 hours

**Recommended Approach**: Perform migration during low-traffic hours or maintenance window.

---

## Support & Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Check credentials in .env and traccar.xml
   - Verify MySQL user permissions
   - Check MySQL is running: `systemctl status mysql`

2. **WebSocket Not Connecting**
   - Check service status: `systemctl status gpswox-socket`
   - Verify ports are open: `netstat -tulpn | grep 9001`
   - Check Redis is running: `redis-cli ping`

3. **Traccar Not Receiving Data**
   - Check Traccar logs: `tail -f /opt/traccar/logs/tracker-server.log`
   - Verify ports are open: `netstat -tulpn | grep 6000`
   - Test device connection with telnet

4. **Apache/PHP Errors**
   - Check error logs: `tail -f /var/log/httpd/gpswox_error.log`
   - Verify PHP-FPM is running: `systemctl status php-fpm`
   - Check file permissions: `ls -la /var/www/html`

5. **Redis Connection Issues**
   - Verify Redis is running: `systemctl status redis`
   - Test connection: `redis-cli ping`
   - Check Redis logs: `journalctl -u redis -f`

---

## Emergency Contacts

- Database Admin: [Contact Info]
- System Administrator: [Contact Info]
- Network Team: [Contact Info]
- DNS Provider: [Contact Info]

---

## Notes

- Always test in a staging environment first if possible
- Keep old server running for at least 1-2 weeks after migration
- Monitor new server closely for first 48 hours
- Have rollback plan ready
- Document any deviations from this plan
- Update this document with lessons learned

