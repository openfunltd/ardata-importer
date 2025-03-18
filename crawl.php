<?php

if (!file_exists(__DIR__ . '/list.json')) {
    throw new Exception('need list.json');
}
if (!file_exists(__DIR__ . '/files')) {
    mkdir(__DIR__ . '/files');
}
$obj = json_decode(file_get_contents(__DIR__ . '/list.json'));
foreach ($obj->data as $data) {
    $zipFileName = $data->zipFileName;
    $downloadZip = $data->downloadZip;
    $target = __DIR__ . "/files/{$zipFileName}";
    $downloadZip = preg_replace_callback('#=([^=&]*)#', function($m) {
        return '=' . urlencode($m[1]);
    }, $downloadZip);
    $url = "https://ardata.cy.gov.tw" . $downloadZip;
    error_log($url);
    if (!file_exists($target) or !filesize($target)) {
        system(sprintf("curl %s > %s", escapeshellarg($url), escapeshellarg($target)));
    }
}
