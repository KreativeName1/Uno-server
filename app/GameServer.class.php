<?php
namespace App;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class GameServer  implements MessageComponentInterface {
  private Collection $rooms;
  private string $nextRoomIdentifier;

  private UserManager $userManager;

  public function __construct() {
    $this->rooms = new Collection();
    $this->userManager = new UserManager();
    $this->nextRoomIdentifier =  sha1(uniqid());
  }

  // When a new connection is opened, log it
  public function onOpen(ConnectionInterface $conn) {
    echo "New connection! ({$conn->resourceId})\n";
  }


  // When a connection is closed, remove the player from all rooms
  public function onClose(ConnectionInterface $conn) {
    echo "Connection {$conn->resourceId} has disconnected\n";
    foreach ($this->rooms as $room) {
      $room->removePlayer($conn);
    }
  }

  public function onError(ConnectionInterface $conn, Exception $e) {
    echo "An error has occurred: {$e->getMessage()}\n";
    $conn->close();
  }

  public function onMessage(ConnectionInterface $from, mixed $msg) {
    echo "Message from {$from->resourceId}: $msg\n";

    $data = json_decode($msg);

    // if its a join message, add the player to the room with the given id
    // if the player does not exist, send an error message
    // if the player is not logged in, send an error message
    // if the player is already in a room, send an error message
    // if the room does not exist, create a new room with the player as the host
    if ($data->type === "join") {

      $room = $this->rooms->get($data->roomId);

      try {
        $user = $this->userManager->getUser($data->username);
      } catch (Exception $e) {
        $from->send("User not found");
        return;
      }

      if ($user->isLoggedIn === false) {
        $from->send("User not logged in");
        return;
      }

      foreach ($this->rooms as $room) {
        if ($room->players->contains($user)) {
          $from->send("User already in a room");
          return;
        }
      }

      $user->connection = $from;

      if ($room === null) {
        $room = new Room($this->nextRoomIdentifier);
        $this->nextRoomIdentifier = sha1(uniqid());
        $this->rooms->add($room);
        $user->isHost = true;
        $from->send(json_encode([
          "type" => "room",
          "roomId" => $room->getIdentifier()
        ]));
      }

      $room->addPlayer($user);
    }

    // if its a start message and the player is the host, start the game.
    else if ($data->type === "start") {
      $room = $this->rooms->get($data->roomId);
      if ($room->players->get(0)->connection === $from) {
        $room->startGame();
      }
    }

    // Registers a new User using the UserManager
    else if ($data->type === "register") {
      $this->userManager->register($data->username, $data->password);
    }

    // Logs in a User using the UserManager
    else if ($data->type === "login") {
      $this->userManager->login($data->username, $data->password);
    }

    // Handles game actions using the Game class
    else if ($data->type === "gameAction") {
      $room = $this->rooms->get($data->roomId);
      $room->game->handleAction($data, $from);
    }


    // if the message type is invalid, send an error message
    else {
      $from->send("Invalid message type");
    }
  }
}