<?php
namespace App;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/DBConnection.class.php';
require __DIR__ . '/GameServer.class.php';
require __DIR__ . '/UserManager.class.php';
require __DIR__ . '/Player.class.php';
require __DIR__ . '/Collection.class.php';
require __DIR__ . '/Card.class.php';
require __DIR__ . '/Game.class.php';
require __DIR__ . '/Room.class.php';
require __DIR__ . '/CardColor.enum.php';
require __DIR__ . '/CardType.enum.php';



use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\GameServer;

$port = 8080;


$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new GameServer()
    )
  ),
  $port
);

echo "Server running on port $port\n";
$server->run();


