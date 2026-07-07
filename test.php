<?php

try {
    $pdo = new PDO(
        "sqlsrv:Server=192.168.100.33;Database=PSADBLIVE_v2",
        "sa",
        "your_password_here"
    );

    echo "Connected!\n";
} catch (PDOException $e) {
    echo $e->getMessage();
}
