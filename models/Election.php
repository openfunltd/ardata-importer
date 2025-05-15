<?php

class Election extends Model
{
    protected static $table = 'election';
    protected static $primary_key = 'path';
    protected static $schema = [
        'path',
        'accountType',
        'yearOrSerial',
        'electionYear',
        'electionName',
        'electionArea',
        'type',
        'pdfFileName',
        'csvFileName',
        'zipFileName',
        'isBackend',
        'downloadPdf',
        'downloadCsv',
        'downloadZip',
    ];

    protected function cast($key, $value)
    {
        if ($key == 'isBackend') {
            return $value ? 1 : 0;
        }
        return $value;
    }
}
