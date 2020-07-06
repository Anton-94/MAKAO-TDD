<?php

namespace Makao\Collection;

use Makao\Card;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\MethodNotAllowedException;

class CardCollection implements \Countable, \Iterator, \ArrayAccess
{
	const FIRST_CARD_INDEX = 0;
	private array $cards = [];
	private int $position = 0;

	public function __construct(array $cards = [])
	{
		$this->cards = $cards;
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->cards);
	}

	public function add(Card $card): self
	{
		$this->cards[] = $card;
		return $this;
	}

	public function addCollection(CardCollection $cardCollection): self
	{
		foreach (clone $cardCollection as $card) {
			$this->add($card);
		}

		return $this;
	}

	public function pickCard(int $index = self::FIRST_CARD_INDEX): Card
	{
		if (empty($this->cards)) {
			throw new CardNotFoundException('You can not pick card from empty CardCollection!');
		}

		$pickedCard = $this->offsetGet($index);
		$this->offsetUnset($index);
		$this->cards = array_values($this->cards);

		return $pickedCard;
	}

	public function valid(): bool
	{
		return $this->offsetExists($this->position);
	}

	public function current(): ?Card
	{
		return $this->cards[$this->position];
	}

	public function next(): void
	{
		++$this->position;
	}

	public function key(): int
	{
		return $this->position;
	}

	public function rewind(): void
	{
		$this->position = self::FIRST_CARD_INDEX;
	}

	public function offsetExists($offset): bool
	{
		return isset($this->cards[$offset]);
	}

	public function offsetGet($offset): Card
	{
		return $this->cards[$offset];
	}

	public function offsetSet($offset, $value): void
	{
		throw new MethodNotAllowedException();
	}

	public function offsetUnset($offset): void
	{
		unset($this->cards[$offset]);
	}

	public function toArray(): array
	{
		return $this->cards;
	}

    public function getLastCard(): Card
    {
        if ($this->count() === 0) {
            throw new CardNotFoundException('You can not get last card from empty CardCollection!');
        }

        return $this->offsetGet($this->count() - 1);
    }
}