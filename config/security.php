<?php

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self'");
header("X-XSS-Protection: 1; mode=block");

?>