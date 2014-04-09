<?php

/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */

// APP
// - the name of this application - will be used within outgoing emails
define('CFG_APP_NAME', 'Web Arbyte');
// - default locale to load? de_DE or en_EN
define('CFG_LOCALE_DEFAULT', 'en_EN');

// DATABASE
// - the database host
define('CFG_DB_HOST', 'localhost');
// - the database user
define('CFG_DB_USER', 'root');
// - the database password
define('CFG_DB_PASSWORD', 'root');
// - the name of the database
define('CFG_DB_DATABASE', 'webchef_webarbyte');


// DATABASE TABLE NAMES
// - table for storing users
define('CFG_DBT_USERS', 'webchef_users');
// - table for storing roles
define('CFG_DBT_USER_ROLES', 'webchef_user_roles');
// - table for storing user sessions
define('CFG_DBT_USER_SESSIONS', 'webchef_user_sessions');

// MAIL
// - the name in email from field
define('CFG_MAIL_FROM_NAME', 'Web Arbyte');
// - the email in from field
define('CFG_MAIL_FROM', 'post@yourdomain.com');
// - do you want to use a smtp server for outgoing emails?
define('CFG_MAIL_USE_SMTP', false);
// - domain or ip of smtp server
define('CFG_MAIL_SMTP_SERVER', 'mail.yourdomain.com');
// - the smtp email user
define('CFG_MAIL_SMTP_USER', 'post@yourdomain.com');
// - the smtp email password
define('CFG_MAIL_SMTP_PWD', '');
// - enable smtp authentication
define('CFG_MAIL_SMTP_AUTH', true);
// - enable encrytion, ssl also accepted
define('CFG_MAIL_SMTP_SECURE', 'tls');
// - smtp port, likely to be 25, 465 or 587, leave empty to auto detect
define('CFG_MAIL_SMTP_PORT', '');

// FILES
// - only this file extensions will be uploaded through uploadify plugin
$allowedFileExtensions = array('jpg','jpeg','gif','png','pdf','zip','rar','doc','docx','xls','xlsx','dmg'); // File extensions

// SESSION
// - the session index
define('CFG_SESSION_INDEX', 'webArbyte');
// - the time limit for an active session in minutes
define('CFG_SESSION_LIMIT', 60);
?>
