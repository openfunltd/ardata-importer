<?php

class Election extends Model
{
    protected static $table = 'election';
    protected static $primary_key = 'id';
    protected static $schema = [
        'id',
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
        $election_year = $this->electionYear;
        $election_name = $this->electionName;
        $election_area = $this->electionArea;
        $year_or_serial = $this->yearOrSerial;

        $id = (empty($election_area)) ?
            "$election_year-$election_name-$year_or_serial" :
            "$election_year-$election_name-$election_area-$year_or_serial";

        $this->id = $id;
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
}
