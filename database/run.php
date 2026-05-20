<?php 

require_once __DIR__ . '/../config/bootstrap.php';

$dbhost   = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbport   = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_NAME'] ?? 'flux';
$dbuser   = $_ENV['DB_USER'] ?? 'root';
$dbpass   = $_ENV['DB_PASS'] ?? '';

$command = $argv[1] ?? null;

if (!$command || !in_array($command, ['migrate', 'seed'], true)) {
    exit("Uso: php run-db.php [migrate|seed]\n");
}

$path = $command === 'migrate' ? MIGRATIONS_PATH : SEEDS_PATH;

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$pdo = new PDO("mysql:host={$dbhost};port={$dbport};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass, $options);

$files = glob($path . '/*.sql');
sort($files);

foreach ($files as $file) {
    echo "Executando: " . basename($file) . PHP_EOL;
    $sql = file_get_contents($file);

    if ($command === 'migrate') {
        preg_match('/-- up(.*)-- down/s', $sql, $matches);
        $sql = trim($matches[1] ?? '');
    }

    if (!empty($sql)) {
        $pdo->exec($sql);
    }
}

echo ucfirst($command) . " finalizado.\n";
