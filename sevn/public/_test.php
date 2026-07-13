<?php
echo "PHP: " . PHP_VERSION . PHP_EOL;
echo "INI: " . php_ini_loaded_file() . PHP_EOL;
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . PHP_EOL;
echo "MySQLi: " . (extension_loaded('mysqli') ? 'YES' : 'NO') . PHP_EOL;
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=amzn-bd-new;charset=utf8mb4', 'amzn-bd-new', 'Wi3aRNNfcCkxf52J');
    echo "DB Connect: OK" . PHP_EOL;
} catch (Exception \$e) {
    echo "DB Connect: FAIL - " . \$e->getMessage() . PHP_EOL;
}
