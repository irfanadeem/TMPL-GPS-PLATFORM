---
description: Temperature & Humidity API Walkthrough
---

# Temperature & Humidity API Walkthrough

This document provides a comprehensive guide to using the Temperature and Humidity monitoring API. This API allows for real-time and historical monitoring of sensor data for GPS tracking devices.

## Overview

The API is built as part of the core telematics platform, following standard RESTful principles and integrating with the existing authentication and authorization systems.

- **Base URL:** `https://jazz.telematicsmaster.com/api`
- **Data Table:** `sensor_readings`
- **Authentication:** Standard API Hash (`user_api_hash`)

---

## Authentication

All requests require a valid API hash. You can provide this in two ways:

1.  **Query Parameter:** `?user_api_hash={your_hash}`
2.  **HTTP Header:** `user-api-hash: {your_hash}`

---

## Endpoints

### 1. Get Latest Sensor Readings
Get the most recent temperature and humidity data for a specific device.

**Request:**
`GET /api/temperature/latest`

**Parameters:**
| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `device_id` | string | Yes | The ID of the device. |

**Example Response:**
```json
{
  "status": 1,
  "message": "Success",
  "data": {
    "device_id": "3199",
    "device_name": "Refrigerated Truck 1",
    "timestamp": "2026-01-02 11:45:00",
    "temperature": {
      "value": 25.0,
      "unit": "°C",
      "status": "normal"
    },
    "humidity": {
      "value": 65.0,
      "unit": "%",
      "status": "normal"
    }
  }
}
```

---

### 2. Get Temperature History
Retrieve historical sensor data with optional aggregation.

**Request:**
`GET /api/temperature/history`

**Parameters:**
| Parameter | Type | Required | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `from_date` | string | Yes | - | Start date (`YYYY-MM-DD HH:MM:SS`). |
| `to_date` | string | Yes | - | End date (`YYYY-MM-DD HH:MM:SS`). |
| `device_id` | string | No | null | Device ID. If omitted, returns data for all accessible devices. |
| `interval` | string | No | `hourly` | Aggregation: `raw`, `hourly`, `daily`. |

**Aggregation Details:**
- **Raw:** Returns every recorded reading in the time period.
- **Hourly/Daily:** Returns the average temperature and humidity for each interval.

---

### 3. Get Fleet Summary
Get aggregated statistics and latest values for all devices in your fleet.

**Request:**
`GET /api/temperature/fleet-summary`

**Parameters:**
| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `from_date` | string | Yes | Start of the statistical period. |
| `to_date` | string | Yes | End of the statistical period. |

---

## Sensor Status Thresholds

The `latest` endpoint automatically categorizes sensor values into statuses:

### Temperature (°C)
- **Normal:** -10°C to 40°C
- **Warning:** 40°C to 50°C OR -20°C to -10°C
- **Critical:** > 50°C OR < -20°C

### Humidity (%)
- **Normal:** 30% to 70%
- **Warning:** 70% to 85% OR 20% to 30%
- **Critical:** > 85% OR < 20%

---

## Implementation Notes for Developers

### Database structure
The data is stored in the `sensor_readings` table. Developers wishing to push data into the system should use the following schema:
- `device_id` (string)
- `sensor_type` ('temperature' or 'humidity')
- `sensor_value` (decimal)
- `timestamp` (datetime)

### Security
Access is strictly enforced based on device ownership and user permissions. Users can only retrieve data for devices they are authorized to manage within the platform.
