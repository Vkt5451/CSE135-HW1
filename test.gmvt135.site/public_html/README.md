# CSE 135 — Analytics & REST Project Submission (Team + Server Info)

## Team Members
- Gautam Mohandas  
- Vincent Trinh  

---

## Server / Login Information 

### Droplet
- Provider: DigitalOcean
- OS: Ubuntu
- Web Server: Apache
- Database: MySQL
- Public IP: `142.93.30.117`

### Server Grader Account 
- Username: `grader`
- Password: `grader`

- (Just In Case)  
- Username: `gmohandas`
- Password: `Paulgeorge13`

### Site Access Credentials
- **Username:** [gmohandas]
- **Password:** [Paulgeorge13]

## Site Links & Content
* **Site URL:** [https://gmvt135.site](https://gmvt135.site)

---

## Site Links

### Target / Test Site
- https://test.gmvt135.site  

### Collector Feed
- https://collector.gmvt135.site/report.php

### Reporting / REST API
- https://reporting.gmvt135.site/api.php  

### Main Domain
- https://gmvt135.site  

---

## File Paths

### Collecter.js
 - /var/www/test.gmvt135.site/collector.js

### api.php
 - /var/www/test.gmvt135.site/

### report.php
 - /var/www/collector.gmvt135.site/report.php

## Database Information

- Database Name: `collector_db`
- Table Name: `activity_log`
- Storage includes:
  - `id`
  - `session_id`
  - `data_type`
  - `event_name`
  - `payload` (JSON)
  - `created_at`

---

## Session Tracking 

We implemented persistent session tracking using:

- `sessionStorage`
- `crypto.randomUUID()` for session ID generation
- `_collector_sid` key stored in browser session storage
- Every payload (static, performance, activity) includes:
  - `sessionId`
  - `url`
  - `page`
  - `timestamp`
  - `timestampISO`

This allows all logged data to be grouped by `session_id` in the database.

---

## Three Types of Data Collected

### Static Data
Collected after page load
Includes the requirments listed on instructions

- User agent string  
- Language  
- Cookies enabled  
- JavaScript enabled  
- CSS enabled (manual detection)  
- Images enabled (manual detection)  
- Screen dimensions  
- Window dimensions  
- Network connection type  

---

### Performance Data
Collected after the `load` event using `setTimeout()` to ensure timing values are populated.

Includes all requirements from instructions:

- startLoad  
- endLoad  
- totalLoadTimeMs  
- Full Navigation Timing object (`nav.toJSON()`)

---

### Activity Data
Continuously collected:
Includes all requirements from instructions

- page_enter  
- page_leave (with durationMs)  
- click (coordinates + button)  
- scroll (throttled)  
- mousemove (throttled)  
- key_down / key_up  
- idle detection (2+ seconds, with duration)   - only extra thing here was we made it ignore miniscule movements to avoid exessive logging
- JS runtime errors  
- Resource load failures  
- Unhandled promise rejections  

---

## REST API Routes

Hosted on:

https://reporting.gmvt135.site/api.php

| Method | Route | Description |
|--------|--------|------------|
| GET | /api.php | Returns latest 100 records |
| GET | /api.php?id={id} | Returns specific record |
| POST | /api.php | Inserts new record |
| PUT | /api.php?id={id} | Marks record as REVIEWED |
| DELETE | /api.php?id={id} | Deletes record |

All routes were tested using `curl`.



Extra Notes:
We checked everything was working with the collector by interacting with test site and then just refreshing https://collector.gmvt135.site/report.php. We had to reset some server permission things so if needed can run sudo chown to regain access within server. 