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
//TODO record 的新舊資料比對現在有誤，要補上 election_id, account_id, party_id
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
    $id = buildId($class, $data);
    $data['id'] = $id;
    $existing_row = $class::find($id);

    //insert data not in db yet
    if (is_null($existing_row)) {
        $row = new $class($data);
        $row->save();
        error_log("{$table} data inserted: {$id}");
    }
}

//table with version control (account, party, record)
function import_row_versioning($table, $data)
{
    $class = ucfirst($table);
    $id = buildId($class, $data);
    $data['id'] = $id;
    $existing_row = $class::find($id);

    if (is_null($existing_row)) {
        $row = new $class($data);
        $row->save();
        error_log("{$table} data inserted: {$id}");
    } elseif (!isSameData($existing_row->toOriginalArray(), $data)) {
        $existing_row->update($data);
        error_log("{$table} data updated: {$id}");
    }
}

function buildId($class, $data) {
    if ($class == 'Election') {
        $election_year = $data['electionYear'];
        $election_name = $data['electionName'];
        $election_area = $data['electionArea'] ?? '';
        $year_or_serial = $data['yearOrSerial'];

        $id = (empty($election_area)) ?
            "$election_year-$election_name-$year_or_serial" :
            "$election_year-$election_name-$election_area-$year_or_serial";

        return $id;
    }

    if ($class == 'Account') {
        $election_year = $data['electionYear'];
        $election_name = $data['electionName'];
        $election_area = $data['electionArea'] ?? '';
        $name = $data['name'];
        $year_or_serial = $data['yearOrSerial'];

        $id = (empty($election_area)) ?
            "$election_year-$election_name-$name-$year_or_serial" :
            "$election_year-$election_name-$election_area-$name-$year_or_serial";

        return $id;
    }

    if ($class == 'Party') {
        $political_party_code = $data['politicalPartyCode'];
        $name = $data['name'];
        $year_or_serial = $data['yearOrSerial'];
        $id = "$political_party_code-$name-$year_or_serial";

        return $id;
    }

    if ($class == 'Record') {
        $pk = $class::getPrimaryKey();
        return $data[$pk];
    }
}

function isSameData($old_data, $new_data)
{
    foreach ($old_data as $key => $old_value) {
        if (!array_key_exists($key, $new_data)) {
            $new_data[$key] = null;
        }
    }

    if ($old_data != $new_data) {
        foreach ($old_data as $key => $old_value) {
            if ($old_value != $new_data[$key]) {
                var_dump($key, $old_value, $new_data[$key]);
            }
        }
    }
    return $old_data == $new_data;
}
