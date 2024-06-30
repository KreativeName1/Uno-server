<?php
namespace App;

class Card {
  public CardType $type;
  public ?int $number;
  public ?CardColor $color;

  public function __construct(CardType $type, ?int $number  = null, ?CardColor $color = null) {
    $this->type = $type;
    if ($type == CardType::Number) {
      $this->number = $number;
    }
    if ($type != CardType::Wild && $type != CardType::WildDrawFour) {
      $this->color = $color;
    }
  }
}