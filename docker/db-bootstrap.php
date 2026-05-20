<?php
// Tiny helper for entrypoint.sh — counts tables and imports the seed dump
// using the same PDO driver Laravel uses. We deliberately avoid Alpine's
// mariadb-client ("mysql-client" on apk): it can't authenticate against
// MySQL 8's default caching_sha2_password plugin, while PHP's mysqlnd can.

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_DATABASE');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');

if ($host === false || $host === '' || !$db || !$user) {
    fwrite(STDERR, "ERR: DB_HOST / DB_DATABASE / DB_USERNAME must be set in env\n");
    exit(2);
}

$action = $argv[1] ?? '';

try {
    // For table-count we don't select the DB so the query works even if
    // it hasn't been created yet. For import we connect into it.
    $dsn = ($action === 'table-count')
        ? "mysql:host=$host;port=$port;charset=utf8mb4"
        : "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "ERR: PDO connect failed: " . $e->getMessage() . "\n");
    exit(1);
}

switch ($action) {
    case 'table-count':
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?"
        );
        $stmt->execute([$db]);
        echo $stmt->fetchColumn();
        exit(0);

    case 'import':
        $file = $argv[2] ?? '';
        if (!is_readable($file)) {
            fwrite(STDERR, "ERR: cannot read $file\n");
            exit(2);
        }
        try {
            // mysqlnd supports multi-statement via exec(); standard mysqldump
            // output (CREATE TABLE + INSERTs + conditional /*! */ comments)
            // imports cleanly this way.
            $pdo->exec(file_get_contents($file));
            exit(0);
        } catch (PDOException $e) {
            fwrite(STDERR, "ERR: import failed: " . $e->getMessage() . "\n");
            exit(1);
        }

    default:
        fwrite(STDERR, "Usage: db-bootstrap.php <table-count|import> [file]\n");
        exit(2);
}
