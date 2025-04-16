<?php

include(__DIR__ . '/init.inc.php');

if (!file_exists(__DIR__ . '/list.json')) {
    throw new Exception('need list.json');
}
$obj = json_decode(file_get_contents(__DIR__ . '/list.json'));
foreach ($obj->data as $data) {
    unset($data->downloadPdf);
    unset($data->downloadCsv);
    unset($data->downloadZip);

    foreach (['incomes', 'expenditures'] as $t) {
        $zipFileName = $data->zipFileName;
        $target = __DIR__ . "/files/{$zipFileName}";
        $zip = new ZipArchive;
        $zip->open($target);
        $file = "{$t}.csv";
        $index = $zip->locateName($file);
        if ($index === false) {
            error_log("{$zipFileName} no {$file}");
            continue;
        }
        $fp = $zip->getStream($file);
        $cols = fgetcsv($fp);
        $idx = 0;
        while ($rows = fgetcsv($fp)) {
            $idx ++;
            if (count($cols) != count($rows)) {
                error_log($idx);
            }
            $values = array_combine($cols, $rows);
            $values['path'] = $data->path;
            $values['type'] = $t;
            if (strpos($values['資料更正日期'], '午')) {
                $values['資料更正日期'] = preg_replace_callback('#(.午) (\d\d)#u', function($m){
                    if ($m[1] == '上午') {
                        return $m[2];
                    } else {
                        return 12 + intval($m[2]);
                    }
                }, $values['資料更正日期']);
                $values['資料更正日期'] = str_replace('/', '-', $value['資料更正日期']);
            }
            $values['交易日期'] = sprintf("%04d-%02d-%02d",
                intval(substr($values['交易日期'], 0, 3)) + 1911,
                substr($values['交易日期'], 3, 2),
                substr($values['交易日期'], 5, 2)
            );
            if ($values['存入專戶日期']) {
                $values['存入專戶日期'] = sprintf("%04d-%02d-%02d",
                    intval(substr($values['存入專戶日期'], 0, 3)) + 1911,
                    substr($values['存入專戶日期'], 3, 2),
                    substr($values['存入專戶日期'], 5, 2)
                );
            } else {
                unset($values['存入專戶日期']);
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
            Elastic::dbBulkInsert('record', "{$data->path}-{$idx}", $values);
        }

        $zip->close();
    }

    Elastic::dbBulkInsert('election', $data->path, $data);
}
Elastic::dbBulkCommit();

