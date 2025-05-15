<?php

include(__DIR__ . '/DB.php');
include(__DIR__ . '/Model.php');
include(__DIR__ . '/models/Election.php');
include(__DIR__ . '/models/Account.php');

import('election');
import('account');

function import($table)
{
    if (file_exists("jsonl/{$table}.jsonl")) {
        //only one file
        $filename = "jsonl/{$table}.jsonl";
        import_from_file($table, $filename);
    } elseif (file_exists("jsonl/{$table}-1.jsonl")) {
        //multiple files
        $file_idx = 1;
        $filename = "jsonl/{$table}-{$file_idx}.jsonl";
        while (file_exists($filename)) {
            import_from_file($table, $filename);
            $file_idx++;
        }
    }
}

function import_from_file($table, $filename)
{
    $f = fopen($filename, 'r');

    while (($line = fgets($f)) !== false) {
        $data = json_decode($line, true);
        if ($table == 'election') {
            import_row_no_versioning($table, $data);
        } elseif (in_array($table, ['account', 'party', 'record'])) {
            import_row_versioning($table, $data);
        }
    }
}

//table without any version control (election)
function import_row_no_versioning($table, $data)
{
    $class = ucfirst($table);
    $existing_row = $class::find($data['path']);

    //insert data not in db yet
    if (is_null($existing_row)) {
        $row = new $class($data);
        $row->save();
        error_log("{$table} data inserted: {$data['path']}");
    }
}

//table with version control (account, party, record)
function import_row_versioning($table, $data)
{
    $class = ucfirst($table);
    $existing_row = $class::find($data['path']);

    if (is_null($existing_row)) {
        $row = new $class($data);
        $row->save();
        error_log("{$table} data inserted: {$data['path']}");
    } elseif (!isSameData($data, $existing_row->toOriginalArray())) {
        $existing_row->update($data);
        error_log("{$table} data updated: {$data['path']}");
    }
}

function isSameData($data_a, $data_b) {
    ksort($data_a);
    ksort($data_b);
    return $data_a === $data_b;
}
