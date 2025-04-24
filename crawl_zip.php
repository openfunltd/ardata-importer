<?php

//select 專戶, 選舉 and 政黨
$type = $argv[1] ?? null;
$types = ['account', 'election', 'party'];

// make sure argument is provided
if (is_null($type) or !in_array($type, $types)) {
    throw new Exception("need argument 'account', 'election' or 'party'\n");
}
// make sure the jsonl is provided
if (!file_exists(__DIR__ . "/lists/{$type}.jsonl")) {
    throw new Exception("need /lists/{$type}.jsonl");
}

// make sure storage directory is exist
if (!file_exists(__DIR__ . '/files')) {
    mkdir(__DIR__ . '/files');
}

// donwload zip files one by one
$f = fopen(__DIR__ . "/lists/{$type}.jsonl", 'r');
while(!feof($f)) {
    $line = fgets($f);
    $data = json_decode($line, false);
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
