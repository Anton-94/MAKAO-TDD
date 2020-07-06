<?php

namespace Makao;

use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;

class Player
{
	private string $name;
    private int $roundToSkip = 0;
	private CardCollection $cardCollection;

	public function __construct(string $name, CardCollection $cardCollection = null)
	{
		$this->name = $name;
		$this->cardCollection = $cardCollection ?? new CardCollection();
	}

	public function __toString(): string
	{
		return $this->name;
	}

	public function getCards(): CardCollection
	{
		return $this->cardCollection;
	}

	public function pickCard(int $index = 0): Card
	{
		return $this->getCards()->pickCard($index);
	}

    public function takeCards(CardCollection $cardCollection, int $count = 1): self
	{
		for($i = 0; $i < $count; $i++) {
			$this->cardCollection->add($cardCollection->pickCard());
		}
		return $this;
	}

    public function sayMakao(): string
	{
		return 'Makao';
	}

    public function pickCardByValue(string $cardValue): Card
	{
		return $this->pickCardByValueAndColor($cardValue);
	}

    public function pickCardsByValue(?string $cardValue): CardCollection
    {
        $cardCollection = new CardCollection();

        try {
            while ($card = $this->pickCardByValue($cardValue)) {
                $cardCollection->add($card);
            }
        } catch (CardNotFoundException $exception) {
            if (0 === $cardCollection->count()) {
                throw new CardNotFoundException('Player has not cards with value ' . $cardValue);
            }
        }

        return $cardCollection;
    }

    public function pickCardByValueAndColor(string $value, string $color = null): Card
    {
        foreach ($this->getCards() as $index => $card) {
            if ($value === $card->getValue() && is_null($color) || $color === $card->getColor()) {
                return $this->pickCard($index);
            }
        }

        $message = 'Player has not card with value ' . $value;
        if (!is_null($color)) {
            $message .= ' and color ' . $color;
        }

        throw new CardNotFoundException($message);
    }

    public function getRoundToSkip(): int
	{
		return $this->roundToSkip;
	}

    public function canPlayRound(): bool
	{
		return $this->getRoundToSkip() === 0;
	}

    public function addRoundToSkip(int $rounds = 1): self
	{
		$this->roundToSkip = $rounds;

		return $this;
	}

    public function skipRound(): self
	{
		--$this->roundToSkip;

		return $this;
	}
}