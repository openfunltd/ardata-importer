<?php

include(__DIR__ . '/Elastic.php');
if (file_exists(__DIR__ . '/config.inc.php')) {
    include(__DIR__ . '/config.inc.php');
}
include(__DIR__ . '/DB.php');
include(__DIR__ . '/Model.php');
include(__DIR__ . '/models/Election.php');

import('election', '2025-05-15');

function import($table, $updated_date = null)
{
    if (is_null($updated_date)) {
        $updated_date = date('Y-m-d');
    }
    $class = ucfirst($table);

    //create index if not exists
    if (!Elastic::indexExists($table)) {
        Elastic::createIndex($table, new stdClass());
    }

    $rows = $class::where(['updatedDate' => $updated_date]);
    foreach ($rows as $row) {
        Elastic::dbBulkInsert($table, $row->getPrimaryKeyValue(), $row->toElasticData());
    }
    Elastic::dbBulkCommit();
}
