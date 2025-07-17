<?php

declare(strict_types = 1);

use Symfony\Component\HttpFoundation\Response;

$response = new Response();
$response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
