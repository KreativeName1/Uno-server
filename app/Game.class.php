<?php

namespace App;

use Ratchet\ConnectionInterface;

class Game
{

  public Collection $players;
  public Collection $deck;
  public Collection $discardPile;
  public int $currentPlayerIndex;
  public bool $reverse;
  public int $drawAmount;
  public bool $drawActive;

  public bool $wildCardActive;
  public ?CardColor $wildCardColor;
  public bool $gameOver;

  public readonly int $CARD_AMOUNT;

  public function __construct(Collection $players)
  {
    $this->CARD_AMOUNT = 7;

    $this->players = $players;
    $this->deck = new Collection();
    $this->reverse = false;
    $this->drawAmount = 0;
    $this->drawActive = false;
    $this->gameOver = false;
  }


  // Starts the game. Creates a deck of UNO cards, shuffles it and deals cards to the players.
  public function startGame(): void
  {
    $this->deck = $this->createDeck();
    $this->deck->shuffle();
    $this->dealCards();
    $this->discardPile->add($this->deck->pop());
    $this->currentPlayerIndex = 0;

    $this->sendWholeState();
  }


  // Sends the whole game state to all players in the game.
  public function sendWholeState(): void
  {
    foreach ($this->players as $player) {
      $player->connection->send(json_encode([
        "type" => "gameState",
        "game" => [
          "players" => $this->players->toArray(),
          "currentPlayerIndex" => $this->currentPlayerIndex,
          "reverse" => $this->reverse,
          "drawAmount" => $this->drawAmount,
          "drawActive" => $this->drawActive,
          "wildCardActive" => $this->wildCardActive,
          "wildCardColor" => $this->wildCardColor,
          "gameOver" => $this->gameOver,
          "discardPile" => $this->discardPile,
          "deck" => $this->deck->toArray(),
        ]
      ]));
    }
  }

  // Sends a message to all players in the game. The message is a JSON string.
  public function send($data): void
  {
    foreach ($this->players as $player) {
      $player->connection->send(json_encode($data));
    }
  }


  // Creates a deck of UNO cards.
  // The deck contains 108 cards.
  // 4 colors: Red, Yellow, Green, Blue
  // 2 sets of cards for each number 0-9
  // 2 sets of cards for each action card: Skip, Reverse, DrawTwo
  // 4 Wild cards
  // 4 Wild Draw Four cards
  public function createDeck(): Collection
  {
    $deck = new Collection();
    $colors = [CardColor::Red, CardColor::Yellow, CardColor::Green, CardColor::Blue];
    foreach ($colors as $color) {
      $deck->add(new Card(CardType::Number, 0, $color));
      for ($i = 1; $i <= 9; $i++) {
        $deck->add(new Card(CardType::Number, $i, $color));
        $deck->add(new Card(CardType::Number, $i, $color));
      }
      $deck->add(new Card(CardType::Skip, null, $color));
      $deck->add(new Card(CardType::Skip, null, $color));
      $deck->add(new Card(CardType::Reverse, null, $color));
      $deck->add(new Card(CardType::Reverse, null, $color));
      $deck->add(new Card(CardType::DrawTwo, null, $color));
      $deck->add(new Card(CardType::DrawTwo, null, $color));
    }
    for ($i = 0; $i < 4; $i++) {
      $deck->add(new Card(CardType::Wild));
      $deck->add(new Card(CardType::WildDrawFour));
    }
    return $deck;
  }

  // Shuffles the deck of cards.
  public function shuffle(): void
  {
    $this->deck->shuffle();
  }


  // Deals cards to all players in the game.
  // Each player gets a certain amount of cards defined by the CARD_AMOUNT constant.
  // The cards are removed from the deck and added to the player's hand.
  public function dealCards(): void
  {
    foreach ($this->players as $player) {
      for ($i = 0; $i < $this->CARD_AMOUNT; $i++) {
        $player->cards->add($this->deck->pop());
      }
    }
  }


  // Plays a card from a player's hand.
  // The card will be added to the discard pile.
  // If the card is a DrawTwo card, the drawAmount will be increased by 2.
  // If the card is a WildDrawFour card, the drawAmount will be increased by 4.
  // If the card is a Wild card, the wildCardActive flag will be set to true and the wildCardColor will be set to the card's color.
  // If the card is a Reverse card, the reverse flag will be toggled.
  // The currentPlayerIndex will be set to the next player.
  // If the player has UNO, a message will be sent to all players.
  // If the player has no cards left, the game will be over and a message will be sent to all players.
  public function playCard(Card $card, Player $player): void
  {

    if ($this->canPlayCard($card, $player) && $this->hasTurn($player)) {
      $this->discardPile->add($card);
      $player->cards->remove($card);

      if ($card->type == CardType::DrawTwo) {
        $this->drawAmount += 2;
        $this->drawActive = true;
      } else if ($card->type == CardType::WildDrawFour) {
        $this->drawAmount += 4;
        $this->drawActive = true;
        $this->wildCardActive = true;
        $this->wildCardColor = $card->color;
      } else if ($card->type == CardType::Wild) {
        $this->wildCardActive = true;
        $this->wildCardColor = $card->color;
      } else {
        $this->wildCardActive = false;
        $this->wildCardColor = null;
        $this->drawActive = false;
        $this->drawAmount = 0;
      }

      $this->reverse = $card->type == CardType::Reverse ? !$this->reverse : $this->reverse;

      $this->currentPlayerIndex = $this->getNextPlayerIndex();

      if ($this->hasUno($player)) {
        $this->send([
          "type" => "playerHasUno",
          "player" => $player
        ]);
      }

      if ($this->hasWon($player)) {
        $this->gameOver = true;
        $this->send([
          "type" => "gameOver",
          "winner" => $player
        ]);
        $this->gameOver();
      }
    }
  }


  // Draws a card from the deck and adds it to the player's hand.
  // If the drawActive flag is set, the player will draw the drawAmount of cards.
  // If the deck is empty, the discard pile will be shuffled and added to the deck.
  // The top card of the discard pile will be added to the new discard pile.
  // Returns the drawn cards.
  public function drawCard(Player $player): Collection
  {
    $drawnCards = new Collection();
    if ($this->drawActive) {
      for ($i = 0; $i < $this->drawAmount; $i++) {
        $player->cards->add($this->deck->pop());
        $drawnCards->add($player->cards->get($player->cards->count() - 1));


        if ($this->deck->count() == 0) {
          $this->deck = $this->discardPile;
          $this->deck->shuffle();
          $this->discardPile->clear();
          $this->discardPile->add($this->deck->pop());
          $this->send([
            "type" => "deckReshuffled"
          ]);
        }
      }
    } else {
      $player->cards->add($this->deck->pop());
      $drawnCards->add($player->cards->get($player->cards->count() - 1));
    }
    $this->drawAmount = 0;
    $this->drawActive = false;
    $this->wildCardActive = false;
    $this->wildCardColor = null;

    return  $drawnCards;
  }


  // Checks if a player can play a card.
  // A player can play a card if the card has the same color or number as the top card of the discard pile.
  // If the last card played was a wild card, the player can play the card if it has the same color as the wild card.
  // If the drawActive flag is set, the player can only play DrawTwo or WildDrawFour cards.
  public function canPlayCard(Card $card, Player $player): bool
  {
    if ($this->drawActive && $card->type != CardType::DrawTwo && $card->type != CardType::WildDrawFour) {
      return false;
    }
    $topCard = $this->discardPile->get($this->discardPile->count() - 1);
    if ($card->type == CardType::Wild || $card->type == CardType::WildDrawFour) {
      if ($this->wildCardActive) {
        if ($card->color == $this->wildCardColor) {
          return true;
        }
        return false;
      }
    }
    if ($card->color == $topCard->color || $card->number == $topCard->number) {
      return true;
    }
    return false;
  }


  // Checks if a player has the turn to play.
  public function hasTurn(Player $player): bool
  {
    return $this->players->get($this->currentPlayerIndex) === $player;
  }


  // Checks if the player has UNO.
  // A player has UNO if they have only one card left.
  public function hasUno(Player $player): bool
  {
    return $player->cards->count() == 1;
  }

  // Checks if the player has won the game.
  // A player has won the game if they have no cards left.
  public function hasWon(Player $player): bool
  {
    return $player->cards->count() == 0;
  }

  // get the index of the next player in the game.
  // if the game is in reverse, the index will be decremented.
  // if the index is out of bounds, it will be set to the first or last player.
  public function getNextPlayerIndex(): int
  {
    $nextPlayerIndex = $this->currentPlayerIndex + ($this->reverse ? -1 : 1);
    if ($nextPlayerIndex < 0) {
      $nextPlayerIndex = $this->players->count() - 1;
    }
    if ($nextPlayerIndex >= $this->players->count()) {
      $nextPlayerIndex = 0;
    }
    return $nextPlayerIndex;
  }


  // Resets the game state.
  public function gameOver()
  {
    $this->players->clear();
    $this->deck->clear();
    $this->discardPile->clear();
    $this->currentPlayerIndex = 0;
    $this->reverse = false;
    $this->drawAmount = 0;
    $this->drawActive = false;
    $this->wildCardActive = false;
    $this->wildCardColor = null;
    $this->gameOver = false;
  }


  // Handles the action of a player.
  // Gets called in the Room class when a player sends a message to the server.
  public function handleAction($data, Player $player)
  {
    if ($player === null) {
      return;
    }

    // Create a new card object from the data and play the card.
    // Send a message to all players that the card has been played by the player.
    if ($data->type === "playCard") {
      $card = new Card($data->card->type, $data->card->number, $data->card->color);
      $this->playCard($card, $player);
      $this->send([
        "type" => "cardPlayed",
        "player" => $player,
        "card" => $card
      ]);

      // Draw a card from the deck and send a message to all players
      // which cards have been drawn by the player.
    } else if ($data->type === "drawCard") {
      $drawnCards = $this->drawCard($player);
      $this->send([
        "type" => "drawnCards",
        "player" => $player,
        "cards" => $drawnCards->toArray()
      ]);
    }


    // if the message type is invalid, send an error message
    else {
      $player->connection->send("Invalid message type");
    }
  }
}
