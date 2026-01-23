# CSE 135 — HW1 Submission (Team + Server Info)

## Team Members
- Gautam Mohandas  
- Vincent Trinh

---

## Server / Login Information (for Graders)

### Droplet
- Provider: DigitalOcean
- OS: Ubuntu
- Public IP: `142.93.30.117`

### Server Grader Account (REQUIRED)
- Username: `grader`
- Password: `grader`

### Site Access Credentials
- **Username:** [gmohandas]
- **Password:** [Paulgeorge13]

## Site Links & Content
* **Site URL:** [https://gmvt135.site](https://gmvt135.site)



## Config Summary

### Github Auto-Deploy Setup
-
-

### Compression
- Enabled gzip to handle our compression 
- Within devtools noticed that the Accept-Encoding changed to display this: gzip, deflate, br, zstd
- Also noticed the Etag changed to: 62d-6490a0c25bb90-gzip
- The Content-Encoding changed to: gzip
- Lastly we notced that the transferred size also got a lot smaller 


### Removing Server Header
- First tried the basic approach we found online whohc was to use mod_headers. So we made our .conf to override the server header using this but for some reason it returned our default server header everytime. So we did some more digging and found that we could use ModSecurity to fully overrite the header as well. So we installed that and that configured it to set our server name to CSE135 Server. We used SecServerSignature and SecRuleEngine on to implement our final working version.
  
