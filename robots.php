<?php
require_once __DIR__ . '/config/security_headers.php';
send_security_headers(false);
header('Content-Type: text/plain; charset=UTF-8');
echo "User-agent: *
";
echo "Allow: /
";
echo "Disallow: /config/
";
echo "Disallow: /helpers/
";
echo "Disallow: /middleware/
";
echo "Disallow: /database/
";
echo "Disallow: /docs/
";
echo "Disallow: /scripts/

";
echo "Sitemap: https://blue-mantis-483450.hostingersite.com/sitemap.xml
";
