<?php

include(__DIR__ . '/init.inc.php');

//select 專戶, 選舉or 政黨
$type = $argv[1] ?? null;
$types = ['account', 'election', 'party'];

// make sure argument is provided
if (is_null($type) or !in_array($type, $types)) {
    throw new Exception("need argument 'account', 'election' or 'party'\n");
}
//check /lists/{$type}.jsonl exists
if (!file_exists(__DIR__ . "/lists/{$type}.jsonl")) {
    throw new Exception("need /lists/{$type}.jsonl");
}

$f = fopen(__DIR__ . "/lists/{$type}.jsonl", 'r');
while(!feof($f)) {
    $line = fgets($f);
    $data = json_decode($line, false);

    $data->downloadPdf = 'https://ardata.cy.gov.tw' . $data->downloadPdf;
    $data->downloadCsv = 'https://ardata.cy.gov.tw' . $data->downloadCsv;
    $data->downloadZip = 'https://ardata.cy.gov.tw' . $data->downloadZip;
    $zipFileName = $data->zipFileName;
    $path = $data->path;

    //get csv in zip, transform data and import data into elasticsearch
    //Record::import($zipFileName, $type, $path);

    Elastic::dbBulkInsert($type, $data->path, $data);
}
fclose($f);
Elastic::dbBulkCommit();
