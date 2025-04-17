# 政治獻金資料備份

## crawl
- curl 'https://ardata.cy.gov.tw/api/v1/search/elections?page=1&pageSize=500' > list.json
- php crawl.php

## import
- wget -o 'https://lydata.ronny-s3.click/ardata.tgz'
- tar zxvf ardata.tgz
- php import.php

## 相關連結
- [資料下載](https://docs.google.com/spreadsheets/d/1v4x-X_Rert2xUCSz7rv1FtcCkzbLekYat2iNs3lUcqA/edit?gid=0#gid=0)
