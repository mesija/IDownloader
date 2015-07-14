<?php
//* -------------------------- Config -------------------------- *//

error_reporting(0);                                               // відключаємо помилки
define('I_FOLDER',            basename(__DIR__));                 // папка в якій розміщений скріпт
define('CSV_FOLDER',          'csv');                             // папка де лежать файли csv
define('PACK',                25000);                             // кількість файлів на один запит
define('DOWNLOAD_FOLDER',     'download');                        // папка з завантаженими зображеннями
define('PROCESS',             10);                                // кількість потоків      * не зловживати ^_^
define('DB_HOST',             'localhost');                       // хост для підключення до апп
define('DB_NAME',             'test');                            // імя бази для підключення до апп
define('DB_USER',             'root');                            // юзер для підключення до апп
define('DB_PASS',             '');                                // пароль для підключення до апп
define('UPDATE_SERVER',       'http://update.mesija.net/');       // сервер оновлень
define('PROXY_SERVER',        '127.0.0.1:1111, 127.0.0.2:1111');  // список проксі серверів
define('PROXY_AUTH',          'user:pass');                       // австоризація для проксі

//* -------------------------- Config -------------------------- *//