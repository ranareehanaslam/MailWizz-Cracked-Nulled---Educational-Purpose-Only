# MailWizz Cracked (Nulled) - Educational Purpose Only

**DISCLAIMER:** This repository is for educational purposes only. Unauthorized use of cracked or nulled software is illegal and unethical. It is strongly advised to purchase legitimate licenses from the official vendors to support the developers and respect their intellectual property rights.

## Table of Contents

1. [Introduction](#introduction)
2. [Features](#features)
3. [Installation](#installation)
4. [Usage](#usage)
5. [Troubleshooting](#troubleshooting)
6. [Contributing](#contributing)
7. [License](#license)

## Introduction

MailWizz is a powerful and versatile email marketing application. This repository contains a cracked (nulled) version of MailWizz for educational purposes only. This version allows users to explore and understand the functionalities of MailWizz without purchasing a license. However, it is crucial to purchase a legitimate license for production use.

## Features

- **Campaign Management:** Create and manage email marketing campaigns with ease.
- **Subscriber Management:** Import, export, and manage subscribers effectively.
- **Analytics and Reporting:** Track the performance of your campaigns with detailed reports.
- **Automation:** Set up automated workflows to streamline your email marketing efforts.
- **Template Management:** Use and manage email templates for consistent branding.
- **API Integration:** Integrate MailWizz with other applications using its robust API.

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- Composer (for dependency management)

### Steps

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/yourusername/mailwizz-cracked.git
   cd mailwizz-cracked
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   ```

3. **Set Up the Environment:**
   - Copy the `.env.example` file to `.env` and configure your database and other settings.
   ```bash
   cp .env.example .env
   nano .env
   ```

4. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

5. **Set Up the Web Server:**
   - Configure your web server to point to the `public` directory of the application.
   - Ensure the web server is properly set up to handle PHP files.

6. **Run the Installer:**
   - Open your browser and navigate to `http://yourdomain.com/install`.
   - Follow the on-screen instructions to complete the installation process.

## Usage

Once installed, you can access the MailWizz dashboard through your browser. Use the credentials set during the installation process to log in. From the dashboard, you can manage campaigns, subscribers, templates, and more.

## Troubleshooting

If you encounter any issues during installation or usage, consider the following steps:

- Ensure all prerequisites are met and properly configured.
- Check the application logs for any error messages.
- Verify database configurations and migrations.
- Consult the [MailWizz documentation](https://www.mailwizz.com/kb/) for additional guidance.

## Contributing

Contributions to this educational project are welcome. If you have suggestions for improvements or have discovered bugs, please open an issue or submit a pull request.

## License

This project is licensed under the MIT License. However, please note that this repository contains a cracked version of MailWizz, which is intended for educational purposes only. For legitimate use, please purchase a license from the [official MailWizz website](https://www.mailwizz.com/).

---

**Note:** Unauthorized use of cracked or nulled software can have legal consequences. Always consider ethical and legal implications before using such software.








# Optimizing RSPAMD and Postfix for High-Volume Email Delivery: A Complete Guide

This guide covers how to optimize RSPAMD and Postfix configurations for high concurrency and efficient email processing, ensuring that your server can handle large volumes of email (e.g., 10,000 emails per minute). By following these steps, you'll achieve improved performance, minimize delays, and ensure successful email delivery. Additionally, we will review network and disk optimization, system resource utilization, and email authentication setups.

---

## 1. RSPAMD Configuration

RSPAMD plays an essential role in filtering spam and handling email requests efficiently. Since it is integrated with Postfix, optimizing RSPAMD helps maximize email throughput while minimizing delays.

### Increase RSPAMD Worker Processes
To handle more email requests concurrently, increase the number of worker processes. Adjust the configuration by modifying the `/etc/rspamd/rspamd.conf` or `/etc/rspamd/local.d/worker-normal.inc` file.

**Example Configuration:**

```bash
worker "normal" {
    bind_socket = "localhost:11333";
    count = 50;  # Increase the number of RSPAMD workers (adjust based on system resources)
    .include "$CONFDIR/worker-normal.inc"
    .include(try=true; priority=1,duplicate=merge) "$LOCAL_CONFDIR/local.d/worker-normal.inc"
    .include(try=true; priority=10) "$LOCAL_CONFDIR/override.d/worker-normal.inc"
}
```

With 32 CPU cores available, setting the `count` to 16 or higher will enable RSPAMD to handle multiple simultaneous connections.

### Enable Async Processing
To improve the speed of email filtering, enable asynchronous processing in `rspamd.conf`.

**Example Configuration:**

```bash
options {
    async = true;
}
```

### Disable Fuzzy Storage (Optional)
If fuzzy hashing is unnecessary for your use case, disabling it can free up system resources.

**Example Configuration:**

```bash
worker "fuzzy" {
    bind_socket = "localhost:11335";
    count = 0;  # Disable fuzzy worker
    .include "$CONFDIR/worker-fuzzy.inc"
    .include(try=true; priority=1,duplicate=merge) "$LOCAL_CONFDIR/local.d/worker-fuzzy.inc"
    .include(try=true; priority=10) "$LOCAL_CONFDIR/override.d/worker-fuzzy.inc"
}
```

### Optimize Timeouts and Max Connections
To manage the high volume of requests, adjust the timeout values and connection limits.

**Example Configuration:**

```bash
options {
    max_children = 100;  # Maximum number of child processes
    max_requests = 5000;  # Max requests per worker
    server_max_connections = 2000;  # Increase max simultaneous connections to RSPAMD
    timeout = 15s;  # Set timeout for long connections
}
```

### Greylisting (Optional)
If greylisting is enabled, it could introduce delays, especially for high-volume email systems. Disabling it can improve delivery speeds if you're confident in your email quality.

**Example Configuration:**

```bash
greylisting = no;
```

---

## 2. Postfix Configuration

Postfix is your Mail Transfer Agent (MTA), and optimizing it ensures that emails are delivered efficiently and in parallel. 

### Increase Concurrent Delivery
To process more emails simultaneously, increase the number of concurrent delivery processes Postfix can handle. Modify `/etc/postfix/main.cf` to increase these limits.

**Example Configuration:**

```bash
default_process_limit = 200  # Max concurrent processes for delivery
smtp_destination_concurrency_limit = 100  # Concurrent deliveries to each destination
smtp_destination_rate_delay = 0s  # Disable delay between emails sent to the same destination
```

These settings are suitable for a server with 32 cores and 2GB of RAM.

### Optimize the Queue
Increase the maximum number of messages Postfix can queue, and ensure that messages are processed quickly.

**Example Configuration:**

```bash
queue_minfree = 50G  # Ensure at least 50GB of free disk space for the queue
message_size_limit = 102400000  # Max message size (100MB)
```

### Maximize Concurrent Connections to Other MTAs
To reduce delays when connecting to external mail servers, increase the number of simultaneous connections Postfix makes.

**Example Configuration:**

```bash
smtp_max_connections = 500  # Max simultaneous connections to external mail servers
```

### Enable Connection Caching
Connection caching allows Postfix to reuse SMTP connections, reducing the overhead of establishing new connections for every message.

**Example Configuration:**

```bash
smtp_connection_cache_on_demand = yes  # Cache SMTP connections for reuse
```

### Increase Limits on Recipients and Connections
To speed up delivery to multiple recipients, increase the recipient limits and the number of cached destinations.

**Example Configuration:**

```bash
smtp_recipient_limit = 1000  # Max number of recipients per message
smtp_connection_cache_destinations = 1000  # Max destinations to cache
```

---

## 3. Optimizing Network and Disk Performance

### Disk I/O
Ensure that your disk subsystem can handle the high volume of emails being queued and processed. For better performance, consider upgrading from HDDs to SSDs, especially for systems under heavy load.

### Network
Make sure your network bandwidth is sufficient to handle the high volume of outgoing emails. If using cloud infrastructure or a datacenter, ensure that bandwidth is adequate to support sending emails at high rates (e.g., 10,000 emails per minute).

---

## 4. Server Load and Monitoring

Monitoring system resources during peak loads is crucial to ensure that the server remains responsive. Use monitoring tools like `top`, `htop`, or `glances` to keep track of CPU and memory usage.

**Example Commands:**

```bash
top
htop
```

You can also monitor Postfix and RSPAMD logs for potential bottlenecks or issues.

**Log Monitoring:**

```bash
tail -f /var/log/mail.log
tail -f /var/log/rspamd/rspamd.log
```

---

## 5. DKIM, SPF, and DMARC Configuration

To ensure that your emails are delivered successfully and not marked as spam, configure DKIM, SPF, and DMARC properly.

### SPF
Ensure that your DNS SPF record is set up correctly to allow your server to send emails on behalf of your domain.

### DKIM
Configure DKIM signing in Postfix to digitally sign outgoing emails, which improves email deliverability and trustworthiness.

### DMARC
Set up DMARC to monitor and enforce email authentication policies, ensuring that emails sent from your domain are properly authenticated.

---

## 6. Backup MX and IP Rotation (Optional)

To protect against blacklisting and ensure email deliverability in case of IP blocks, consider setting up multiple IPs for email delivery. This can be achieved through Postfix’s multi-instance setup or DNS round-robin.

---

## 7. Example of Final Postfix Configuration

Here’s an example of a complete Postfix configuration (`main.cf`) after applying the above optimizations:

```bash
# Main configuration
default_process_limit = 200
smtp_destination_concurrency_limit = 50
smtp_destination_rate_delay = 0s
queue_minfree = 50G
message_size_limit = 102400000
smtp_max_connections = 500
smtp_connection_cache_on_demand = yes
smtp_recipient_limit = 1000
smtp_connection_cache_destinations = 1000

# Network and security
smtpd_tls_security_level = may
smtp_tls_security_level = may
smtpd_use_tls = yes
smtpd_sasl_auth_enable = yes
```

---

## 8. Email Delivery Testing

Before sending large volumes of emails (e.g., 10,000), it’s advisable to test your configuration with smaller batches (e.g., 500-1,000 emails) to ensure that the system is handling them smoothly. Monitor the delivery process to ensure that there are no failures or delays.

---

## Additional System-Level Recommendations

Given your server's specifications (32 CPU cores and 32GB of RAM), it's crucial to ensure that system resources are fully utilized. You may need to adjust limits on processes and open files.

### Modify `/etc/security/limits.conf`

```bash
* soft nproc 65535
* hard nproc 65535
* soft nofile 65535
* hard nofile 65535
```

This will allow both Postfix and RSPAMD to create enough processes and open files to handle the high volume of email traffic.

---

By following the configurations outlined above, your server will be better equipped to handle large-scale email delivery, ensuring both speed and reliability.
