<?php

include(__DIR__ . '/Elastic.php');
if (file_exists(__DIR__ . '/config.inc.php')) {
    include(__DIR__ . '/config.inc.php');
}
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

    $count = $class::count(['updatedDate' => $updated_date]);
    if ($count == 0) {
        return;
    }

    $per_page = 10000;
    $total_pages = ceil($count / $per_page);

    for ($page = 1; $page <= $total_pages; $page++) {
        $rows = $class::where(['updatedDate' => $updated_date], $per_page, $page);
        foreach ($rows as $row) {
            Elastic::dbBulkInsert($table, $row->getPrimaryKeyValue(), $row->toElasticData());
        }
        Elastic::dbBulkCommit();
    }
}
