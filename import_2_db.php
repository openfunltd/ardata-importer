<?php

include(__DIR__ . '/DB.php');
include(__DIR__ . '/Model.php');
include(__DIR__ . '/models/Election.php');

$f = fopen('lists/election.jsonl', 'r');

while (($line = fgets($f)) !== false) {
    $data = json_decode($line, true);
    $election = new Election($data);
    $election->save();
}
