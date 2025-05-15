<?php

class Account extends Model
{
    protected static $table = 'account';
    protected static $primary_key = 'path';
    protected static $schema = [
        'path',
        'accountNumber',
        'accountType',
        'yearOrSerial',
        'version',
        'electionYear',
        'electionName',
        'electionName2',
        'electionArea',
        'name',
        'type',
        'pdfFileName',
        'csvFileName',
        'zipFileName',
        'isBackend',
        'downloadPdf',
        'downloadCsv',
        'downloadZip',
        'updatedDate'
    ];

    public function update($data)
    {
        //write old data into table account_history
        $old_data = [];
        foreach ($this->attributes as $key => $value) {
            if ($key == 'updatedDate') {
                $key = 'versionedDate';
            }
            $old_data[$key] = $this->cast($key, $value);
        }
        $columns = array_keys($old_data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO " . static::$table . '_history' .
            "(" . implode(', ', $columns) . ")" .
            " VALUES (" . implode(', ', $placeholders) . ")";

        $db = DB::getInstance()->pdo;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);

        //update data
        $data['updatedDate'] = date('Y-m-d');
        return parent::update($data);
    }

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
}
