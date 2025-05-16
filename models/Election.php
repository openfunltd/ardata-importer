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
        'updatedDate',
    ];

    public function save()
    {
        $this->updatedDate = date('Y-m-d');
        return parent::save();
    }

    protected function cast($key, $value)
    {
        if ($key == 'isBackend') {
            return $value ? 1 : 0;
        }
        return $value;
    }

    protected function uncast($key, $value)
    {
        if ($key == 'isBackend') {
            if ($value == 1) {
                return true;
            } elseif ($value == 0) {
                return false;
            }
        }
        return $value;
    }

    protected static $elastic_index_mappings = [
        'path' => 'keyword',
        'accountType' => 'keyword',
        'yearOrSerial' => 'integer',
        'electionYear' => 'integer',
        'electionName' => 'keyword',
        'electionArea' => 'keyword',
        'type' => 'keyword',
        'pdfFileName' => 'keyword',
        'csvFileName' => 'keyword',
        'zipFileName' => 'keyword',
        'isBackend' => 'boolean',
        'downloadPdf' => 'keyword',
        'downloadCsv' => 'keyword',
        'downloadZip' => 'keyword',
    ];

    public static function getElasticIndexMappings()
    {
        $data = (object) [];
        $properties = (object) [];
        foreach (static::$elastic_index_mappings as $key => $type) {
            $field = (object) ['type' => $type];
            $properties->{$key} = $field;
        }
        $data->properties = $properties;

        return $data;
    }
}
