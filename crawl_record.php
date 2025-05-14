<?php

//每次爬取之間的暫停秒數
$sleep_sec = $argv[1] ?? 0;
$sleep_sec = (int) $sleep_sec;

//make sure directory /lists exists
if (!file_exists(__DIR__ . '/lists')) {
    mkdir(__DIR__ . '/lists');
}

//get pages of endpoints
$url_ardata_api = 'https://ardata.cy.gov.tw/api/v1/search/';
$record_params = "&orderBy=Date&orderDirection=ASC&keywordRanges=Candidate&keywordRanges=PoliticalParty&keywordRanges=Donor&keywordRanges=ExpenditureTarget";
$url = $url_ardata_api . "?page=1&pageSize=1000" . $record_params;
$ret = json_decode(file_get_contents($url, false));
$data = $ret->data;
$pages = $ret->paging->pageCount;
error_log($url);

$data = $ret->data;
storeData($data, 1);

//collect all data if there are more than 1 pages
if ($pages > 1) {
    for ($p = 2; $p <= $pages; $p++) {
        $url = $url_ardata_api . "?page={$p}&pageSize=1000" . $record_params;
        $ret = json_decode(file_get_contents($url, false));
        $data = $ret->data;
        error_log($url);

        storeData($data, $p);

        if ($sleep_sec > 0) {
            sleep($sleep_sec);
        }
    }
}


function storeData($data, $page) {
    //Store data into /lists/{$endpoint}.jsonl
    $f = fopen(__DIR__ . "/lists/record-{$page}.jsonl", 'w');
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
}
