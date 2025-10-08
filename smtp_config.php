<?php
// smtp_config.php

// SMTP Configuration for Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pmadridano2@gmail.com');  // Your Gmail
define('SMTP_PASSWORD', 'tqcr wgip cdzk ufow');     // Gmail App Password
define('SMTP_FROM_EMAIL', 'no-reply@madridanohospital.com');
define('SMTP_FROM_NAME', 'Madridano Hospital');


// Enable SMTP authentication and TLS
define('SMTP_AUTH', true);
define('SMTP_SECURE', 'tls');
?>