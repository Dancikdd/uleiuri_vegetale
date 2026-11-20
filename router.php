<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = str_replace('\\', '/', rawurldecode($requestPath));
foreach (explode('/', trim($requestPath, '/')) as $segment) {
    if (preg_match('/^\.(?:env(?:\.|$)|git(?:$|\/))/i', $segment)) {
        http_response_code(404);
        exit;
    }
}

return false;
