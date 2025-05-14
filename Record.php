<?php

class Record
{
    public static function import($zipFileName, $type, $path)
    {
        foreach (['incomes', 'expenditures'] as $transaction_type) {
            echo "({$zipFileName}-{$transaction_type})\n";
            $ret = self::getZipAndFile($zipFileName, $transaction_type);
            $zip = $ret->zip;
            $fp = $ret->fp;

            self::transfromAndInsertData($fp, $transaction_type, $type, $path);

            $zip->close();
        }
    }

    private static function getZipAndFile($zipFileName, $csvFileName)
    {
        $target = __DIR__ . "/files/{$zipFileName}";
        $zip = new ZipArchive;
        $zip->open($target);
        $fp = $zip->getStream("{$csvFileName}.csv");

        return (object)[
            'zip' => $zip,
            'fp' => $fp,
        ];
    }

    private static function transfromAndInsertData($fp, $transaction_type, $type, $path)
    {
        $cols = fgetcsv($fp);
        $idx = 0;
        while ($row = fgetcsv($fp)) {
            $idx ++;
            if (count($cols) != count($row)) {
                echo "{$idx}\n";
                continue;
            }
            $values = array_combine($cols, $row);
            unset($values['序號']);
            $values["{$type}_path"] = $path;
            $values['type'] = $transaction_type;
            if (strpos($values['資料更正日期'], '午')) {
                $values['資料更正日期'] = preg_replace_callback('#(.午) (\d\d)#u', function($m){
                    if ($m[1] == '上午') {
                        return $m[2];
                    } else {
                        return 12 + intval($m[2]);
                    }
                }, $values['資料更正日期']);
                $values['資料更正日期'] = str_replace('/', '-', $values['資料更正日期']);
            }
            $values['交易日期'] = sprintf("%04d-%02d-%02d",
                intval(substr($values['交易日期'], 0, 3)) + 1911,
                substr($values['交易日期'], 3, 2),
                substr($values['交易日期'], 5, 2)
            );
            if (array_key_exists('存入專戶日期', $values)) {
                if ($values['存入專戶日期']) {
                    $values['存入專戶日期'] = sprintf("%04d-%02d-%02d",
                        intval(substr($values['存入專戶日期'], 0, 3)) + 1911,
                        substr($values['存入專戶日期'], 3, 2),
                        substr($values['存入專戶日期'], 5, 2)
                    );
                } else {
                    unset($values['存入專戶日期']);
                }
            }
            $values['收入金額'] = floatval(str_replace(',', '', $values['收入金額']));
            $values['支出金額'] = floatval(str_replace(',', '', $values['支出金額']));
            if ($values['資料更正日期']) {
                $values['資料更正日期'] = sprintf("%04d-%02d-%02d",
                    intval(substr($values['資料更正日期'], 0, 3)) + 1911,
                    substr($values['資料更正日期'], 3, 2),
                    substr($values['資料更正日期'], 5, 2)
                );
            }

            //Insert data
            Elastic::dbBulkInsert('record_lab', null, $values);
        }
    }
}

