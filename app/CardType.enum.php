<?php
namespace App;

enum CardType {
  case Number;
  case Skip;
  case Reverse;
  case DrawTwo;
  case Wild;
  case WildDrawFour;
}
