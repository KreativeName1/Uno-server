<?php
// Uno Car
enum CardType {
  case Number;
  case Skip;
  case Reverse;
  case DrawTwo;
  case Wild;
  case WildDrawFour;

}
class Card {
  public CardType $type;
  public ?int $number;

  public function __construct(CardType $type, ?int $number) {
    $this->type = $type;
    if ($type == CardType::Number) {
      $this->number = $number;
    }
  }
}