<?php

namespace Tests;

use App\Core\Database;
use PHPUnit\Framework\TestCase;

class SeedDemoUsersTest extends TestCase
{
    public function testDatabaseSeedsDemoUsersWhenEmpty(): void
    {
        $ref = new \ReflectionProperty(Database::class, 'pdo');
        $ref->setAccessible(true);
        $ref->setValue(null, null);

        $dbPath = sys_get_temp_dir() . '/clinic_demo_' . uniqid() . '.sqlite';
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }

        Database::initialize([
            'driver' => 'sqlite',
            'database' => $dbPath,
            'seed_demo_data' => true,
            'environment' => 'development',
        ]);

        $count = Database::getConnection()->query('SELECT COUNT(*) AS total FROM users')->fetch()['total'];

        $this->assertGreaterThan(0, $count);
        $this->assertNotFalse(Database::getConnection()->query("SELECT email FROM users WHERE email = 'patient@example.com'")->fetch());

        $ref = new \ReflectionProperty(Database::class, 'pdo');
        $ref->setAccessible(true);
        $ref->setValue(null, null);

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }

    public function testDatabaseDoesNotSeedDemoUsersInProduction(): void
    {
        $ref = new \ReflectionProperty(Database::class, 'pdo');
        $ref->setAccessible(true);
        $ref->setValue(null, null);

        $dbPath = sys_get_temp_dir() . '/clinic_production_' . uniqid() . '.sqlite';
        Database::initialize([
            'driver' => 'sqlite',
            'database' => $dbPath,
            'seed_demo_data' => true,
            'environment' => 'production',
        ]);

        $count = Database::getConnection()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $this->assertSame(0, (int) $count);

        $ref->setValue(null, null);
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }

    public function testSQLiteDatabaseCannotBeCreatedInsidePublicDirectory(): void
    {
        $ref = new \ReflectionProperty(Database::class, 'pdo');
        $ref->setAccessible(true);
        $ref->setValue(null, null);

        $dbPath = base_path('public/storage/should-not-be-created.sqlite');
        $this->expectException(\InvalidArgumentException::class);
        try {
            Database::initialize([
                'driver' => 'sqlite',
                'database' => $dbPath,
            ]);
        } finally {
            $ref->setValue(null, null);
            $this->assertFileDoesNotExist($dbPath);
        }
    }
}
