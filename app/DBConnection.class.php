<?php

namespace App;

use Exception;
use PDO;
use PDOException;
use Dotenv\Dotenv;

class DBConnection
{
  private string $databasePath;

  public ?PDO $pdo;

  public function __construct()
  {
    $this->databasePath = __DIR__ . "/../data/database.db";
    echo "Database Path: $this->databasePath\n";
    try {
      $this->pdo = new PDO("sqlite:$this->databasePath");
      $this->createUsersTable();
    } catch (PDOException $e) {
      throw new Exception("Database connection failed: " . $e->getMessage());
    }
  }
  private function createUsersTable(): void
  {
    $query = "CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL,
      password TEXT NOT NULL
    )";
    $this->pdo->exec($query);
  }

  public function query(string $query, array $params = [], array $types = []): array
  {
    $stmt = $this->pdo->prepare($query);
    foreach ($params as $key => $value) {
      $type = $types[$key] ?? PDO::PARAM_STR;
      $stmt->bindValue(":$key", $value, $type);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function __destruct()
  {
    $this->pdo = null;
  }
}