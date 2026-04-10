# Xophz Phantom Zone

> **Category:** Castle Walls · **Version:** 0.0.1

Keep your site from becoming a ghost town by identifying dead end links.

## Description

**Phantom Zone** is a 404 monitoring and error logging resolution tool for the COMPASS platform. It actively intercepts site errors and logs them into a custom database table, allowing administrators to identify broken links, missing assets, and potential malicious probes.

### Core Capabilities

- **Error Interception** – Hooks into WordPress `template_redirect` to catch 404s and 500s.
- **Detailed Logging** – Records the requested URL, HTTP status code, standard WP User ID, IP address, and User-Agent.
- **REST API** – Exposes endpoints to retrieve and manage recorded errors.

## Requirements

- **Xophz COMPASS** parent plugin (active)
- WordPress 5.8+, PHP 7.4+

## Installation

1. Ensure **Xophz COMPASS** is installed and active.
2. Upload `xophz-compass-phantom-zone` to `/wp-content/plugins/`.
3. Activate through the Plugins menu.
4. On activation, the plugin initializes the `wp_xophz_phantom_errors` database table.
5. Access via the My Compass dashboard → **Phantom Zone**.

## Database Tables

| Table | Purpose |
|---|---|
| `wp_xophz_phantom_errors` | Stores intercepted errors, URLs, and visitor metadata |

## PHP Class Map

| Class | File | Purpose |
|---|---|---|
| `Xophz_Compass_Phantom_Zone` | `class-xophz-compass-phantom-zone.php` | Core plugin hooks and error interceptor |
| `Xophz_Compass_Phantom_Zone_DB` | `class-xophz-compass-phantom-zone-db.php` | Database schema creation |

## Frontend Routes

| Route | View | Description |
|---|---|---|
| `/phantom-zone` | Dashboard | Error log overview and resolution interface |

## Changelog

### 0.0.1

- Initial release featuring `template_redirect` error interception and database logging.
