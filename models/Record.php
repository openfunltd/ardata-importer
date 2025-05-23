<?php

class Record extends Model
{
    protected static $table = 'record';
    protected static $primary_key = 'id';
    protected static $schema = [
        'id',
        'election_id', //relate to Election.id
        'account_id', //relate to Account.id
        'party_id', //relate to Party.id
        'name',
        'electionName',
        'yearOrSerial',
        'transactionDate',
        'typeCode',
        'type', //base on typeCode
        'donor',
        'donorIdentifier',
        'receivedAmount',
        'donationAmount',
        'payType',
        'saveAccountDate',
        'returnOrPaytrs',
        'donationUse',
        'isMoney', //cast from true/false to 1/0 
        'donorAddress',
        'tel',
        'exposeRemark',
        'rpIntraName',
        'rpIntraTitle',
        'rpPartyName',
        'rpPartyTitle',
        'rpRelationStr',
        'diffVersionStr',
        'updateDatetimeB',
        'updatedDate',
    ];
    public const TYPE_MAP = [
        '11' => '個人捐贈收入',
        '12' => '營利事業捐贈收入',
        '13' => '政黨捐贈收入',
        '14' => '人民團體捐贈收入',
        '15' => '匿名捐贈',
        '16' => '其他收入',
        '20' => '人事費用支出',
        '21' => '宣傳支出',
        '22' => '租用宣傳車輛支出',
        '23' => '租用競選辦事處支出',
        '24' => '集會支出',
        '25' => '交通旅運支出',
        '26' => '雜支支出',
        '27' => '繳庫支出',
        '28' => '返還捐贈支出',
        '29' => '公共關係費用支出',
        '31' => '個人捐贈收入',
        '32' => '營利事業捐贈收入',
        '33' => '人民團體捐贈收入',
        '34' => '匿名捐贈收入',
        '35' => '其他收入',
        '41' => '人事費用支出',
        '42' => '業務費用支出',
        '43' => '公共關係費用支出',
        '44' => '選務費用支出',
        '46' => '雜支支出',
        '47' => '繳庫支出',
        '48' => '捐贈其推薦之公職候選人競選費用支出',
        '49' => '返還捐贈支出',
        '51' => '其他收入',
        '61' => '支付當選後與其公務有關之費用',
        '62' => '捐贈政治團體或所屬政黨支出',
        '63' => '捐贈教育、文化、公益、慈善機構或團體支出',
        '64' => '參加公職人員選舉使用支出',
        '68' => '返還捐贈支出',
        '69' => '繳庫支出',
    ];

    public function toOriginalArray()
    {
        $attributes = $this->attributes;
        unset($attributes['updatedDate']);
        unset($attributes['electionPath']);
        unset($attributes['accountPath']);
        unset($attributes['partyPath']);
        unset($attributes['type']);

        return $attributes;
    }

    public function update($data)
    {
        //write old data into table record_history
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
        $stmt->execute($old_data);

        //update data
        $data['updatedDate'] = date('Y-m-d');
        return parent::update($data);
    }

    public function save()
    {
        $this->updatedDate = date('Y-m-d');
        $this->type = self::TYPE_MAP[$this->typeCode]; //收支科目

        $election_name = $this->electionName;
        $year_or_serial = $this->yearOrSerial;
        $name = $this->name;

        if (!empty($election_name)) {
            //election.path
            $elections = Election::where([
                'electionName' => $election_name,
                'yearOrSerial' => $year_or_serial,
            ]);
            if (!empty($elections)) {
                $this->election_id = $elections[0]->id;
            }
            //account.path
            $accounts = Account::where([
                'electionName' => $election_name,
                'yearOrSerial' => $year_or_serial,
                'name' => $name,
            ]);
            if (!empty($accounts)) {
                $this->account_id = $accounts[0]->id;
            }
        } else {
            //party.path
            $parties = Party::where([
                'name' => $name,
                'yearOrSerial' => $year_or_serial,
            ]);
            if (!empty($parties)) {
                $this->party_id = $parties[0]->id;
            }
        }

        return parent::save();
    }

    protected function cast($key, $value)
    {
        if ($key == 'isMoney') {
            return $value ? 1 : 0;
        }
        return $value;
    }

    protected function uncast($key, $value)
    {
        if ($key == 'isMoney') {
            if ($value == 1) {
                return true;
            } elseif ($value == 0) {
                return false;
            }
        }
        return $value;
    }
}
