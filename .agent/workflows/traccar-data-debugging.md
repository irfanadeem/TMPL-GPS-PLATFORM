---
description: How to Debug Traccar Listener Data Flow
---

# Traccar Data Flow Debugging Guide

This guide explains how to check Traccar listener data that is decoded from protocols and what is being sent to Redis or the database.

## Data Flow Overview

```
GPS Device → Traccar Server → /public/insert.php → Redis Queue → insert:run Command → PositionsWriter → Database → Redis/WebSocket Events
```

## Step 1: Check Traccar Server (Protocol Decoding)

### View Traccar Logs
```bash
# Check if Traccar is receiving data
tail -f /opt/traccar/logs/tracker-server.log

# Look for lines like:
# [device_imei] connected
# [device_imei] HEX: 7878... (raw protocol data)
# [device_imei] id: 123456, time: 2026-02-13 06:00:00, lat: 24.1234, lon: 67.5678
```

### What to Look For:
- **Connection logs**: Device IMEI connecting to Traccar
- **HEX data**: Raw protocol bytes being received
- **Decoded data**: Parsed position data (lat, lon, speed, time, etc.)
- **Errors**: Protocol parsing errors or invalid data

### Check Traccar Configuration
```bash
# View Traccar config
cat /opt/traccar/conf/traccar.xml | grep -A 5 "forward"

# Look for forwarding URL (should point to your insert.php):
# <entry key='forward.url'>http://localhost/insert.php</entry>
```

## Step 2: Check Redis Queue (Data Reception)

### Monitor Redis Keys
```bash
# turbo
# List all position keys in Redis
redis-cli KEYS "positions.*"

# Output example:
# 1) "positions.123456789012345"
# 2) "positions.987654321098765"
```

### Check Queue Length
```bash
# turbo
# Check how many positions are queued for a specific IMEI
redis-cli LLEN positions.123456789012345

# Output: 5 (means 5 positions waiting to be processed)
```

### View Queued Data
```bash
# turbo
# View the first position in queue (without removing it)
redis-cli LINDEX positions.123456789012345 0

# Output (JSON):
# {"uniqueId":"123456789012345","fixTime":"2026-02-13 06:00:00","latitude":24.1234,"longitude":67.5678,"speed":45.5,"course":180,"altitude":100,"attributes":{"ip":"192.168.1.100","ignition":true,"fuel":75}}
```

### Monitor Redis in Real-Time
```bash
# turbo
# Watch Redis commands as they happen
redis-cli MONITOR

# You'll see:
# "LPUSH" "positions.123456789012345" "{\"uniqueId\":\"123456789012345\",...}"
```

## Step 3: Check insert.php (Data Validation)

### Test insert.php Directly
```bash
# Send test data to insert.php
curl -X POST http://localhost/insert.php \
  -d "uniqueId=123456789012345" \
  -d "fixTime=2026-02-13 06:00:00" \
  -d 'attributes={"ignition":true,"fuel":75}'

# Expected response:
# {"status":1}
```

### Check insert.php Logs
```php
// Add debugging to /var/www/html/public/insert.php (temporarily)
// After line 3, add:
file_put_contents('/tmp/insert_debug.log', date('Y-m-d H:i:s') . ' - ' . json_encode($input) . PHP_EOL, FILE_APPEND);
```

Then monitor:
```bash
# turbo
tail -f /tmp/insert_debug.log
```

## Step 4: Check insert:run Process (Queue Processing)

### Verify Process is Running
```bash
# turbo
ps aux | grep "insert:run"

# Expected output:
# www-data  19222  0.0  1.2  php /var/www/html/artisan insert:run
```

### Monitor Process with Debug Mode
```bash
# Stop the current process
pkill -f "insert:run"

# Run in debug mode (foreground)
php /var/www/html/artisan insert:run debug

# You'll see detailed output:
# Getting imei: 0.001
# Getting device: 0.005
# Processing position...
# Writing: Positions 1 Events 0
# Write time 0.025
```

### Check Process Logs
```bash
# turbo
# View Laravel logs for insert process errors
tail -f /var/www/html/storage/logs/laravel-$(date +%Y-%m-%d).log | grep -i "insert\|position"
```

## Step 5: Check Database (Data Storage)

### Verify traccar_devices Table
```sql
-- Check if device has a traccar_device record
SELECT d.id, d.imei, d.name, d.traccar_device_id, td.id as td_id, td.server_time, td.lastValidLatitude, td.lastValidLongitude
FROM devices d
LEFT JOIN traccar_devices td ON d.traccar_device_id = td.id
WHERE d.imei = '123456789012345';

-- Expected: td_id should NOT be NULL
-- server_time should be recent (within last few minutes for active devices)
```

### Check Position Tables
```sql
-- Find the position table for device ID 123
SELECT * FROM positions_123 
ORDER BY time DESC 
LIMIT 5;

-- Shows recent positions with:
-- time, latitude, longitude, speed, course, altitude, other (JSON with sensors)
```

### Monitor Database Updates in Real-Time
```sql
-- In MySQL, watch traccar_devices updates
SELECT id, uniqueId, server_time, lastValidLatitude, lastValidLongitude, speed, protocol
FROM traccar_devices
WHERE server_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
ORDER BY server_time DESC;

-- Run this every few seconds to see new updates
```

## Step 6: Check Redis/WebSocket Events (Broadcasting)

### Monitor Redis Pub/Sub Channels
```bash
# turbo
# Subscribe to all channels
redis-cli PSUBSCRIBE "*"

# You'll see events like:
# Message: {"event":"DevicePositionChanged","data":{"id":123,"lat":24.1234,"lng":67.5678,...}}
```

### Check Specific Device Channel
```bash
# turbo
# Device channels are hashed: md5('device_' . device_id)
# For device ID 123:
echo -n "device_123" | md5sum
# Output: a8f5f167f44f4964e6c998dee827110c

redis-cli SUBSCRIBE a8f5f167f44f4964e6c998dee827110c
```

### Monitor WebSocket Server
```bash
# turbo
# Check if socket server is running
pm2 list

# View socket server logs
pm2 logs socket

# You should see:
# Server listening on port 6001
# Client connected
# Broadcasting DevicePositionChanged to channel...
```

## Step 7: Inspect Decoded Protocol Data

### View Raw Position Data in Database
```sql
-- Check the 'other' column which contains all decoded protocol parameters
SELECT 
    time,
    latitude,
    longitude,
    speed,
    JSON_PRETTY(other) as decoded_data
FROM positions_123
ORDER BY time DESC
LIMIT 1;

-- Output shows all protocol-specific fields:
-- {
--   "ignition": true,
--   "fuel": 75,
--   "temperature": 25.5,
--   "satellites": 12,
--   "hdop": 1.2,
--   "battery": 4.1
-- }
```

### Check Sensor Values
```sql
-- View processed sensor values
SELECT 
    time,
    latitude,
    longitude,
    sensors_values
FROM positions_123
WHERE sensors_values IS NOT NULL
ORDER BY time DESC
LIMIT 5;

-- Output:
-- [{"id":45,"val":75},{"id":46,"val":25.5}]
-- (id = sensor_id, val = calculated value)
```

## Step 8: Common Issues and Solutions

### Issue 1: Data in Redis but Not in Database
**Symptom**: `redis-cli KEYS "positions.*"` shows data, but database not updating

**Check**:
```bash
# Is insert:run process running?
ps aux | grep "insert:run"

# Check for errors
php /var/www/html/artisan insert:run debug
```

**Common Causes**:
- insert:run process crashed
- traccar_devices table missing records
- Position table doesn't exist for device

### Issue 2: Traccar Receiving Data but Not Forwarding
**Symptom**: Traccar logs show data, but Redis is empty

**Check**:
```bash
# Verify forward.url in traccar.xml
cat /opt/traccar/conf/traccar.xml | grep forward

# Test insert.php manually
curl -X POST http://localhost/insert.php -d "uniqueId=test" -d "fixTime=2026-01-01 00:00:00" -d 'attributes={}'
```

**Solution**: Ensure `forward.url` is set and insert.php is accessible

### Issue 3: Database Updates but No WebSocket Events
**Symptom**: Database shows new positions, but WebSocket clients not receiving updates

**Check**:
```bash
# Is Redis pub/sub working?
redis-cli PSUBSCRIBE "*"

# Is socket server running?
pm2 list
pm2 logs socket
```

**Solution**: Restart socket server or check Laravel broadcasting configuration

## Step 9: End-to-End Test

### Complete Data Flow Test
```bash
# 1. Clear Redis queue
redis-cli DEL positions.123456789012345

# 2. Send test position
curl -X POST http://localhost/insert.php \
  -d "uniqueId=123456789012345" \
  -d "fixTime=$(date '+%Y-%m-%d %H:%M:%S')" \
  -d 'attributes={"ignition":true,"fuel":75,"test":true}'

# 3. Verify Redis
redis-cli LLEN positions.123456789012345
# Should show: 1

# 4. Wait for processing (or run insert:run manually)
php /var/www/html/artisan insert:run debug

# 5. Check database
mysql gpswox_web -e "SELECT * FROM traccar_devices WHERE uniqueId='123456789012345' ORDER BY server_time DESC LIMIT 1\\G"

# 6. Monitor WebSocket
redis-cli PSUBSCRIBE "*"
```

## Quick Reference Commands

```bash
# Check entire data flow
redis-cli KEYS "positions.*"                    # Redis queue
ps aux | grep "insert:run"                      # Processing
mysql gpswox_web -e "SELECT COUNT(*) FROM traccar_devices WHERE server_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE);"  # Recent updates
pm2 logs socket --lines 20                      # WebSocket events

# Debug specific device
IMEI="123456789012345"
redis-cli LLEN positions.$IMEI
redis-cli LINDEX positions.$IMEI 0
mysql gpswox_web -e "SELECT * FROM devices WHERE imei='$IMEI'\\G"
```

## Files Reference

- **Data Reception**: `/var/www/html/public/insert.php`
- **Queue Processing**: `/var/www/html/app/Console/Commands/InsertCommand.php`
- **Data Writing**: `/var/www/html/app/Console/PositionsWriter.php`
- **Event Broadcasting**: `/var/www/html/app/Events/DevicePositionChanged.php`
- **WebSocket Server**: `/var/www/html/socket/socket.js`
- **Traccar Config**: `/opt/traccar/conf/traccar.xml`
- **Traccar Logs**: `/opt/traccar/logs/tracker-server.log`
