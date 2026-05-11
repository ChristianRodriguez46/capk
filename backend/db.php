<?php
// backend/db.php
// Single PDO database connection 

function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host   = 'localhost';
        $dbname = 'capk_db';
        $user   = 'capk';
        $pass   = 'SeniorProject26!Capk';

        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }

    return $pdo;
}