# DB Sentinel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/atmos/laravel-db-sentinel.svg?style=flat-square)](https://packagist.org/packages/atmos/laravel-db-sentinel)
[![Total Downloads](https://img.shields.io/packagist/dt/atmos/laravel-db-sentinel.svg?style=flat-square)](https://packagist.org/packages/atmos/laravel-db-sentinel)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Laravel DB Sentinel** is a smart database query monitoring and optimization tool for Laravel 7+. It helps Laravel applications track, analyze and optimize database behavior with insights on slow or inefficient queries and actionable performance feedback.

> **⚠️ Beta Version**  
> **This package is currently in Beta.** Expect frequent updates and potential breaking changes until we reach version 1.0.0. Please report any issues or bugs on the GitHub repository.

## ✨ Features
* 📊 **Quick Dashboard** — A clean UI to visualize your database status.
* 🔍 **Query Monitoring** — Automatically capture and log all database queries executed by your application.
* 🐢 **Slow Query Detection** — Highlight queries that exceed a configurable execution threshold.
* 📈 **Performance Insights** — Collect statistics like query counts, total time spent, and slow query summaries.
* 🧪 **Developer-Friendly Output** — Easy logs or debug output to help you optimize during development.
* 🛡️ **Built-in Authorization:** — Restrict access to specific users via User IDs.
* ⚙️ **Configurable & Lightweight** — Enable or disable features via config — no performance impact in production.

## 📦 Installation

Install **Laravel DB Sentinel** via Composer:

```bash
composer require atmos/laravel-db-sentinel
```
### ⚠️ Important: Run Migrations

After installing the package, you must run:

```bash
php artisan migrate
```

### Laravel Auto-Discovery
This package supports Laravel's package auto-discovery, so no manual registration is required.

If auto-discovery is disabled, manually register the service provider in `config/app.php`:

```php
'providers' => [
    Atmos\DbSentinel\DbSentinelServiceProvider::class,
],
```

## 🔧 Publishing Package Resources

Laravel DB Sentinel allows you to publish its resources so you can customize them inside your application.


### 📄 Publish Configuration File

Publish the configuration file to `config/db-sentinel.php`:

```bash
php artisan vendor:publish --tag=db-sentinel-config
```

---

### 🎨 Publish View Files

If the package provides Blade views, publish them with:

```bash
php artisan vendor:publish --tag=db-sentinel-views
```

Published views will be located in:

```
resources/views/vendor/db-sentinel/
```

You can safely modify these views without affecting the core package.

---

> 💡 Tip: You can run `php artisan vendor:publish` without arguments to interactively choose which resources to publish.

## ⚙️ Configuration

DB Sentinel is designed to work out of the box, but you can customize its behavior using the following environment variables in your `.env` file.

### 🔌 General & Storage
| Variable | Description | Default |
| :--- | :--- | :--- |
| `DB_SENTINEL_ENABLED` | Toggle the entire monitoring system. | `true` |
| `DB_SENTINEL_CONNECTION` | DB connection to store the DB Sentinel logs (e.g., `mysql`). | Your application's default database connection |
| `DB_SENTINEL_LOGS_TABLE` | The name of the table for stored logs. | `sentinel_logs` |
| `DB_SENTINEL_PRUNE_DAYS` | Auto-delete logs older than X days. | `30` |
| `DB_SENTINEL_MAX_SQL` | Truncate SQL queries longer than X chars. | `5000` |

### ⚡ Performance & Scope
| Variable | Description | Default |
| :--- | :--- | :--- |
| `DB_SENTINEL_QUEUE_NAME` | Queue for background analysis jobs. | `default` |
| `DB_SENTINEL_ALLOWED_CONS` | Connections to monitor (comma-separated). | `mysql,mariadb,pgsql,sqlsrv` |
| `DB_SENTINEL_IGNORED_CONS` | Connections to skip (comma-separated). | `sqlite,testing` |

### 🖥️ Dashboard & Security
| Variable | Description | Default |
| :--- | :--- | :--- |
| `DB_SENTINEL_DASHBOARD` | Enable or disable the Web UI. | `true` |
| `DB_SENTINEL_PATH` | The URL slug to access the dashboard. | `db-sentinel` |
| `DB_SENTINEL_USER_IDS` | Authorized User IDs (comma-separated). | *(empty)* |

---

### 📝 Example .env Setup

```bash
# Core Settings
DB_SENTINEL_ENABLED=true
DB_SENTINEL_PRUNE_DAYS=14

# Storage & Connection
DB_SENTINEL_LOGS_TABLE=custom_sentinel_logs
DB_SENTINEL_CONNECTION=mysql

# Dashboard Access
DB_SENTINEL_PATH=admin/db-metrics
DB_SENTINEL_USER_IDS=1,25

```

### ⚠️ Important Logic & Security Notes

#### 🔄 Connection Precedence
When configuring your monitoring scope, please note the following hierarchy:
* **`DB_SENTINEL_IGNORED_CONS` always takes precedence.**
* If a connection is listed in **both** "allowed" and "ignored," it will be **ignored**. 
* Ensure your primary connection is not accidentally added to the ignored list.

#### 🔒 Dashboard Access
> [!CAUTION]
> **Be cautious when setting `DB_SENTINEL_USER_IDS`.**
> This variable controls who can view your database performance metrics and raw SQL queries. 
> * Ensure you only input the **numeric IDs** of trusted administrators.
> * If left empty while `DB_SENTINEL_DASHBOARD` is `true`, access will depend solely on your `auth` middleware, which may be too permissive depending on your app's setup.
