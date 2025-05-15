<?php

include(__DIR__ . '/DB.php');
include(__DIR__ . '/Model.php');
include(__DIR__ . '/models/Election.php');
include(__DIR__ . '/models/Account.php');
include(__DIR__ . '/models/Party.php');
include(__DIR__ . '/models/Record.php');

import('election');
import('account');
import('party');
import('record');

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
            $filename = "jsonl/{$table}-{$file_idx}.jsonl";
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
    $pk = $class::getPrimaryKey();
    $existing_row = $class::find($data[$pk]);

    if ($table == 'record') {
        var_dump("{$table} now on: " . $data[$pk]);
    }

    if (is_null($existing_row)) {
        $row = new $class($data);
        $row->save();
        error_log("{$table} data inserted: {$data[$pk]}");
    } elseif (!isSameData($existing_row->toOriginalArray(), $data)) {
        $existing_row->update($data);
        error_log("{$table} data updated: {$data[$pk]}");
    }
}

function isSameData($old_data, $new_data)
{
    foreach ($old_data as $key => $old_value) {
        if (!array_key_exists($key, $new_data)) {
            $new_data[$key] = null;
        }
    }

    return $old_data == $new_data;
}
