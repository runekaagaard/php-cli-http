#!/usr/bin/env php
<?php
/**
 * Fakes a http request on the command line.
 */

$index_file = $argv[1];
$environment = unserialize(base64_decode($argv[2]));

foreach($environment as $k => $v) {
    $$k = $v;
}
xdebug_disable();
ob_start();
require $index_file;
$html = ob_get_clean();

echo base64_encode(serialize(array(
    'html' => $html,
    'headers' => headers_list(),
    'session' => $_SESSION,
))); 