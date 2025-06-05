<?php

/**
 * @file
 * Add settings for the Fast 404 module.
 */

$settings['fast_404'] = [
  'exclude_paths' => '/\/(?:styles)|(?:system\/files)\//',
  'paths' => '/\.(txt|png|gif|jpg|jpeg|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i',
  'html' => '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>',
];
