<?php

namespace Makao;

use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\TooManyPlayersAtTheTableException;

class Table
{
	private array $players = [];
	private int $currentIndexPlayer = 0;
    private ?string $playedCardColor = null;
	private ?CardCollection $cardDeck;
	private CardCollection $playedCards;

    public function __construct(CardCollection $cardDeck = null, CardCollection $playedCards = null)
	{
		$this->cardDeck = $cardDeck ?? new CardCollection();
		$this->playedCards = $playedCards ?? new CardCollection();

		if (!is_null($playedCards)) {
		    $this->changePlayedCardColor($this->playedCards->getLastCard()->getColor());
        }
	}

	public function countPlayers(): int
	{
		return count($this->players);
	}

	public function addPlayer($player): void
	{
		if (count($this->players) == 4) {
			throw new TooManyPlayersAtTheTableException('too many');
		}

		$this->players[] = $player;
	}

	public function getPlayedCards(): CardCollection
	{
		return $this->playedCards;
	}

	public function getCardDeck(): CardCollection
	{
		return $this->cardDeck;
	}

	public function addCardCollectionToDeck(CardCollection $cardCollection): self
	{
		$this->cardDeck->addCollection($cardCollection);
		return $this;
	}

	public function getCurrentPlayer(): Player
	{
		return $this->players[$this->currentIndexPlayer];
	}

	public function getNextPlayer(): Player
	{
		return $this->players[$this->currentIndexPlayer + 1] ?? $this->players[0];
	}

	public function getPreviousPlayer(): Player
	{
		return $this->players[$this->currentIndexPlayer - 1] ?? $this->players[$this->countPlayers() - 1];
	}

	public function finishRound(): void
	{
		if (++$this->currentIndexPlayer === $this->countPlayers()) {
			$this->currentIndexPlayer = 0;
		}
	}

    public function backRound(): void
    {
        if (--$this->currentIndexPlayer < 0) {
            $this->currentIndexPlayer = $this->countPlayers() - 1;
        }
    }

    public function getPlayedCardColor(): string
    {
        if (!is_null($this->playedCardColor)) {
            return $this->playedCardColor;
        }

        throw new CardNotFoundException('No played card on the table yet!');
    }

    public function addPlayedCard(Card $card): self
    {
        $this->playedCards->add($card);
        $this->changePlayedCardColor($card->getColor());

        return $this;
    }

    public function addPlayedCards(CardCollection $cardCollection): self
    {
        foreach ($cardCollection as $card) {
            $this->addPlayedCard($card);
        }

        return $this;
    }

    public function changePlayedCardColor(string $color): self
    {
        $this->playedCardColor = $color;

        return $this;
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }
}