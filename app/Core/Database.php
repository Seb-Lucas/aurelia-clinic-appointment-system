<?php

namespace App\Core;

if (!function_exists('base_path')) {
    require_once __DIR__ . '/../helpers.php';
}

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function initialize(array $config = []): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = $config['driver'] ?? 'sqlite';
        $dsn = '';
        $username = null;
        $password = null;

        if ($driver === 'sqlite') {
            $database = (string) ($config['database'] ?? base_path('storage/app.sqlite'));
            if ($database !== ':memory:') {
                if (!self::isAbsolutePath($database)) {
                    $database = base_path($database);
                }

                $dir = dirname($database);
                if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
                    throw new \RuntimeException('Unable to create the SQLite database directory.');
                }

                $resolvedDir = realpath($dir);
                if ($resolvedDir === false) {
                    throw new \RuntimeException('Unable to resolve the SQLite database directory.');
                }

                $resolvedDatabase = realpath($database) ?: $resolvedDir . DIRECTORY_SEPARATOR . basename($database);
                $publicRoot = realpath(base_path('public'));
                if ($publicRoot !== false && self::isWithinDirectory($resolvedDatabase, $publicRoot)) {
                    throw new \InvalidArgumentException('SQLite databases must be stored outside the public directory.');
                }

                $database = $resolvedDatabase;
            }
            $dsn = 'sqlite:' . $database;
        } elseif ($driver === 'mysql') {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 3306;
            $database = $config['database'] ?? 'clinic_app';
            $charset = $config['charset'] ?? 'utf8mb4';
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);
            $username = $config['username'] ?? 'root';
            $password = $config['password'] ?? '';
        } else {
            throw new \InvalidArgumentException('Unsupported database driver: ' . $driver);
        }

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }

        self::$pdo = $pdo;
        try {
            self::runMigrations($config);
            self::seedDemoData($config);
        } catch (\Throwable $e) {
            self::$pdo = null;
            throw $e;
        }

        return $pdo;
    }

    public static function getConnection(): PDO
    {
        return self::initialize(config('app.db.' . (config('app.db.default') ?? 'sqlite')));
    }

    public static function runMigrations(array $config = []): void
    {
        self::$pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                migration VARCHAR(255) NOT NULL PRIMARY KEY,
                applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $driver = $config['driver'] ?? 'sqlite';
        $migrationPaths = glob(base_path('database/migrations/*.sql')) ?: [];
        $migrationPaths = array_values(array_filter($migrationPaths, function (string $path) use ($driver): bool {
            $isMysqlMigration = str_ends_with($path, '.mysql.sql');
            return $driver === 'mysql' ? $isMysqlMigration : !$isMysqlMigration;
        }));
        sort($migrationPaths, SORT_STRING);
        if ($migrationPaths === []) {
            throw new \RuntimeException('No database migrations are available for driver: ' . $driver);
        }

        foreach ($migrationPaths as $migrationPath) {
            $migration = basename($migrationPath);
            $applied = self::$pdo->prepare('SELECT 1 FROM schema_migrations WHERE migration = :migration LIMIT 1');
            $applied->execute(['migration' => $migration]);
            if ($applied->fetchColumn() !== false) {
                continue;
            }

            $sql = file_get_contents($migrationPath);
            if ($sql === false) {
                throw new \RuntimeException('Unable to read database migration: ' . $migration);
            }
            if (trim($sql) === '') {
                throw new \RuntimeException('Database migration is empty: ' . $migration);
            }

            $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql, -1, PREG_SPLIT_NO_EMPTY);
            try {
                self::$pdo->beginTransaction();
                foreach ($statements as $statement) {
                    $clean = trim($statement);
                    if ($clean !== '') {
                        self::$pdo->exec($clean);
                    }
                }
                $record = self::$pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:migration)');
                $record->execute(['migration' => $migration]);
                self::$pdo->commit();
            } catch (\Throwable $e) {
                if (self::$pdo->inTransaction()) {
                    self::$pdo->rollBack();
                }
                throw new \RuntimeException('Failed to apply database migration: ' . $migration, 0, $e);
            }
        }
    }

    public static function seedDemoData(array $config = []): void
    {
        if (!(self::$pdo instanceof PDO)) {
            throw new \LogicException('The database must be initialized before demo seeding.');
        }

        if (!($config['seed_demo_data'] ?? false)
            || strtolower((string) ($config['environment'] ?? 'production')) === 'production') {
            return;
        }

        $count = (int) self::$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $seedPath = base_path('database/seeders/001_demo_seed.sql');
        if (!is_file($seedPath)) {
            throw new \RuntimeException('Demo seed file is missing.');
        }

        $sql = file_get_contents($seedPath);
        if ($sql === false || trim($sql) === '') {
            throw new \RuntimeException('Demo seed file could not be read or is empty.');
        }

        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        try {
            self::$pdo->beginTransaction();
            foreach ($statements as $statement) {
                $clean = trim($statement);
                if ($clean !== '') {
                    self::$pdo->exec($clean);
                }
            }
            self::$pdo->commit();
        } catch (\Throwable $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            throw new \RuntimeException('Failed to seed development demo data.', 0, $e);
        }
    }

    private static function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }

    private static function isWithinDirectory(string $path, string $directory): bool
    {
        $prefix = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (DIRECTORY_SEPARATOR === '\\') {
            return str_starts_with(strtolower($path), strtolower($prefix));
        }

        return str_starts_with($path, $prefix);
    }

    public static function reset(): void
    {
        if (self::$pdo instanceof PDO) {
            self::$pdo->exec('DELETE FROM appointments');
            self::$pdo->exec('DELETE FROM doctor_schedules');
            self::$pdo->exec('DELETE FROM users');
            self::$pdo->exec('DELETE FROM patients');
            self::$pdo->exec('DELETE FROM doctors');
            self::$pdo->exec('DELETE FROM departments');
            self::$pdo->exec('DELETE FROM services');
            self::$pdo->exec('DELETE FROM notifications');
            self::$pdo->exec('DELETE FROM audit_logs');
        }
    }
}
