<?php
namespace App;
use App\DBConnection;
use Exception;
class UserManager {

  private Collection $users;
  private DBConnection $db;

  public function __construct() {
    $this->users = new Collection();
    $this->db = new DBConnection();
    $this->loadUsers();
  }

  // Gets all users from the database and stores them in the users collection
  private function loadUsers() {
    $this->users->clear();
    $query = "SELECT * FROM users";
    $users = $this->db->query($query);
    foreach ($users as $user) {
      $player = new Player($user['username']);
      $player->password = $user['password'];
      $this->users->add($player);
    }
  }

  // Get a user by username
  public function getUser(string $username): Player {
    $index = $this->users->indexOf($username);
    if ($index === -1) {
      throw new Exception('User not found');
    }
    return $this->users->get($index);
  }


  // Register a new user
  // Checks if the username already exists in the database
  // Hashes the password and inserts the new user into the database
  // Adds the new user to the users collection
  public function register(string $username, string $password) {
    $user = new Player($username);
    $user->password = $password;

    $query = "SELECT * FROM users WHERE username = :username";
    $params = [
      'username' => $username
    ];
    $users = $this->db->query($query, $params);
    if (count($users) > 0) {
      throw new Exception('Username already exists');
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $params = [
      'username' => $username,
      'password' => $password
    ];
    $this->db->query($query, $params);
    $this->users->add($user);
  }


  // Login a user
  // Checks if the username exists in the database
  // Verifies the password
  // Sets the user as logged in
  // Updates the user in the users collection
  public function login(string $username, string $password) {
    $query = "SELECT * FROM users WHERE username = :username";
    $params = [
      'username' => $username
    ];
    $users = $this->db->query($query, $params);
    if (count($users) == 0) {
      throw new Exception('Username does not exist');
    }

    $user = $users[0];
    if (!password_verify($password, $user['password'])) {
      throw new Exception('Invalid password');
    }
    $user->isLoggedIn = true;

    $this->users->update($this->users->indexOf($user), $user);

    return $user;
  }
}