<?php
namespace App;
use Ratchet\ConnectionInterface;
class Room {

  private string $Identifier;

  private Collection $players;

  public Game $game;

  public function __construct(string $Identifier) {
    $this->Identifier = $Identifier;
  }

  public function getIdentifier(): string {
    return $this->Identifier;
  }

  // add a player to the room
  // if the game has already started, send an error message to the player
  // send a message to all players in the room that a new player has joined
  public function addPlayer(Player $player): void {
    if ($this->game !== null) {
      $player->connection->send(json_encode([
        "type" => "error",
        "message" => "Game already started"
      ]));
      return;
    }

    $this->game->players->add($player);
    foreach ($this->game->players as $p) {
      $p->connection->send(json_encode([
        "type" => "playerJoined",
        "player" => $player
      ]));
    }
  }

  // remove a player from the room
  // send a message to all players in the room that a player has left
  public function removePlayer(Player $player): void {
    $this->game->players->remove($player);
    foreach ($this->game->players as $p) {
      $p->connection->send(json_encode([
        "type" => "playerLeft",
        "player" => $player
      ]));
    }
  }

  // start the game
  // create a new game object with the players in the room
  // send a message to all players in the room that the game has started
  public function startGame(): void {
    $this->game = new Game($this->players);
    $this->game->startGame();

    foreach ($this->game->players as $player) {
      $player->connection->send(json_encode([
        "type" => "gameStarted",
        "game" => $this->game
      ]));
    }
  }


  // handle an action from a player
  // get the player object from the connection
  // pass the action to the game object with the player object
  // the game object will handle the action

  public function handleAction(ConnectionInterface $from, array $data): void {

    $player = null;
    foreach ($this->game->players as $p) {
      if ($p->connection === $from) {
        $player = $p;
        break;
      }
    }
    if ($player === null) {
      $from->send(json_encode([
        "type" => "error",
        "message" => "Player not found"
      ]));
      return;
    }

    $this->game->handleAction($data, $player);
  }


}