<?php

namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use App\DBConnection;

// Assuming your DBConnection class is located in the App namespace
// and your tests are in the Tests namespace

class DBConnectionTest extends TestCase
{
  private ?DBConnection $connection;

  public function setUp(): void
  {
    // Replace these with your actual environment variable names
    putenv('DB_HOST=localhost');
    putenv('DB_PORT=3306');
    putenv('DB_USERNAME=your_username');
    putenv('DB_PASSWORD=your_password');
    putenv('DB_DATABASE=your_database_name');

    $this->connection = new DBConnection();
  }

  public function testConnection(): void
  {
    $this->assertInstanceOf(PDO::class, $this->connection->pdo);
  }

  public function testQuery(): void
  {
    $query = "SELECT 1 + 1 AS result";
    $result = $this->connection->query($query);

    $this->assertEquals(1, count($result));
    $this->assertEquals(2, $result[0]['result']);
  }

  public function tearDown(): void
  {
    $this->connection = null;
  }
}
