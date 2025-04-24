<?php

//select 專戶, 選舉 and 政黨
$type = $argv[1] ?? null;
$endpoints = [
    'account' => 'individuals',
    'election' => 'elections',
    'party' => 'parties',
];
$endpoint = $endpoints[$type] ?? null;

if (is_null($type) or is_null($endpoint)) {
    throw new Exception("need argument 'account', 'election' or 'party'\n");
}

//make sure directory /lists exists
if (!file_exists(__DIR__ . '/lists')) {
    mkdir(__DIR__ . '/lists');
}

//get pages of endpoints
$url_ardata_api = 'https://ardata.cy.gov.tw/api/v1/search/';
$url = $url_ardata_api . $endpoint . "?page=1&pageSize=1000"; //size upper limit is 1000
$ret = json_decode(file_get_contents($url, false));
$pages = $ret->paging->pageCount;
$data = $ret->data;

//collect all data if there are more than 1 pages
if ($pages > 1) {
    for ($p = 2; $p <= $pages; $p++) {
        $url = $url_ardata_api . $endpoint . "?page={$p}&pageSize=1000";
        $ret = json_decode(file_get_contents($url, false));
        $data = array_merge($data, $ret->data);
    }
}

//Store data into /lists/{$endpoint}.jsonl
$f = fopen(__DIR__ . "/lists/{$type}.jsonl", 'w');
$idx = 1;
foreach ($data as $row) {
    $json_line = json_encode($row, JSON_UNESCAPED_UNICODE);
    if ($idx < count($data)) {
        $json_line = $json_line . "\n";
    }
    fwrite($f, $json_line);
    $idx++;
}
fclose($f);
