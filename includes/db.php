<?php
// Database connection (PDO). Edit these for your XAMPP setup.
const DB_HOST = 'localhost';
const DB_NAME = 'library_db';
const DB_USER = 'root';
const DB_PASS = '';          // XAMPP default is an empty password

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Cannot connect to the database. Is MySQL running and did you import sql/schema.sql and sql/data.sql?');
}
