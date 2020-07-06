<?php

namespace Makao\Validator\Validator;

use Makao\Card;
use Makao\Exception\CardDuplicationException;

class CardValidator
{
	public function valid(Card $activeCard, Card $newCard): bool
	{
		if ($activeCard === $newCard) {
			throw new CardDuplicationException($newCard);
		}

		return $activeCard->getColor() === $newCard->getColor()
			|| $activeCard->getValue() === $newCard->getValue()
			|| $newCard->getValue() == Card::VALUE_QUEEN
			|| $activeCard->getValue() === Card::VALUE_QUEEN;
	}
}