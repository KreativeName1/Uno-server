<?php
namespace App;
use Exception;
use PDO;
use PDOException;

class DBConnection {

  private string $host;
  private int $port;
  private string $username;
  private string $password;
  private string $database;

  public ?PDO $pdo;

  public function __construct() {
    $this->readEnv();
    try {
      $this->pdo = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->database", $this->username, $this->password);
      $this->createUsersTable();
    } catch (PDOException $e) {
      throw new Exception("Database connection failed: " . $e->getMessage());
    }
  }
  private function readEnv(): void {
    $this->host = getenv('DB_HOST');
    $this->port = getenv('DB_PORT');
    $this->username = getenv('DB_USERNAME');
    $this->password = getenv('DB_PASSWORD');
    $this->database = getenv('DB_DATABASE');
  }

  private function createUsersTable(): void {
    $query = "CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(50) NOT NULL,
      password VARCHAR(255) NOT NULL
    )";
    $this->pdo->exec($query);
  }



  public function query(string $query, array $params = [], array $types = []): array
  {
    $stmt = $this->pdo->prepare($query);
    $i = 0;
    foreach ($params as $key => $value) {
      $type = isset($types[$key]) ? $types[$key] : PDO::PARAM_STR;
      $stmt->bindValue(":$key", $value, $type);
      $i++;
    }
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

  public function __destruct() {
    $this->pdo = null;
  }
}