<?php
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?? 'NAO ENCONTRADO') . "<br>";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?? 'NAO ENCONTRADO') . "<br>";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?? 'NAO ENCONTRADO') . "<br>";
echo "DB_PASS: " . (($_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS')) ? '***definida***' : 'VAZIA') . "<br>";