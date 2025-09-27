# Perfex CRM - Local Installation

This is a Perfex CRM installation configured for local development and network sharing.

## Features
- Perfex CRM v3.x
- Configured for Laragon on Windows
- Network accessible via IP (192.168.1.88)
- Admin authentication redirect configured

## Installation
1. Install Laragon
2. Clone this repository to `C:\laragon\www\app`
3. Configure database settings in `application/config/app-config.php`
4. Start Laragon services
5. Access via `http://192.168.1.88/admin/authentication`

## Configuration
- Base URL: `http://192.168.1.88/`
- Database: MySQL (configured in app-config.php)
- Apache Virtual Host configured for network access

## Network Access
- Local: `http://localhost`
- Network: `http://192.168.1.88`
- Admin Panel: `http://192.168.1.88/admin/authentication`

## Notes
- Ensure Laragon is running with Apache and MySQL
- Firewall should allow port 80 for network access
- VPN should be disabled for local network access



