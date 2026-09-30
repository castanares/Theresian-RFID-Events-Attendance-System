<?php
namespace App\Config;

class Config {
    // Application Settings
    const APP_NAME = 'STC RFID Event Attendance System';
    const APP_VERSION = '2.0 (MVC)';
    const BASE_URL = '/Theresian-RFID-Events-Attendance-System';
    
    // Database Settings
    const DB_HOST = 'localhost';
    const DB_NAME = 'stc_event_db';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_CHARSET = 'utf8mb4';

    // Security Settings
    const SESSION_LIFETIME = 1800; // 30 minutes inactivity
    const MAX_OTP_ATTEMPTS = 5;
    const OTP_EXPIRY_MINUTES = 10;
    const BACKUP_SECRET = 'stc_autobackup_secret_key_2026';

    // Institutional Branding
    const INSTITUTION_NAME = 'Saint Theresa College of Tandag, Inc.';
    const INSTITUTION_SUBTITLE = 'Office of Student Affairs and Services (OSAS)';
    const LOGO_PATH = '/images/stctandag-logo.png';
}
