<?php
namespace App;
use Ratchet\ConnectionInterface;
class Player {
  public string $username;
  public string $password;
  // list of cards in hand
  public Collection $cards;

  public bool $isHost = false;

  public bool $isReady = false;

  public bool $isLoggedIn = false;

  public ConnectionInterface $connection;

  public int $RoomId;

  public function __construct(string $username, ?Collection $cards = null) {
    $this->username = $username;
    $this->cards = $cards ?? new Collection();
  }

}
