<?php
// Optional Sentry integration (error tracking)
// To enable:
// 1. Install with Composer: composer require sentry/sentry-php
// 2. Set environment variable SENTRY_DSN on the server (do NOT commit it).
// 3. Include this file early in your bootstrap (e.g. in a global include or index.php).

// Example (uncomment to enable):
// if (getenv('SENTRY_DSN')) {
//     \Sentry\init(['dsn' => getenv('SENTRY_DSN')]);
//     // Capture a test message on init
//     \Sentry\captureMessage('Sentry initialized for SIPINTAR-TI');
// }

return;