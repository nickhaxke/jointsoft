<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft;charset=utf8', 'root', 'root');
print_r($pdo->query('SHOW COLUMNS FROM member_contributions')->fetchAll(PDO::FETCH_ASSOC));
