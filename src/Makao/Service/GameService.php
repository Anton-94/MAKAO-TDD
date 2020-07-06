<?php

namespace Makao\Service;

use Makao\Exception\CardNotFoundException;
use Makao\Exception\GameException;
use Makao\Service\CardSelector\CardSelectorInterface;
use Makao\Table;

class GameService
{
    const MINIMAL_PLAYERS = 2;
    const COUNT_START_PLAYED_CARDS = 5;

	private ?Table $table;
	private bool $isStarted = false;
	private CardService $cardService;
    private CardSelectorInterface $cardSelector;
    private CardActionService $cardActiveService;

    public function __construct(Table $table, CardService $cardService, CardSelectorInterface $cardSelector, CardActionService $cardActiveService)
	{
		$this->table = $table;
		$this->cardService = $cardService;
        $this->cardSelector = $cardSelector;
        $this->cardActiveService = $cardActiveService;
    }

	public function isStarted(): bool
	{
		return $this->isStarted;
	}

	public function getTable(): Table
	{
		return $this->table;
	}

	public function addPlayers(array $players): self
	{
		foreach ($players as $player) {
			$this->table->addPlayer($player);
		}

		return $this;
	}

	public function startGame(): void
	{
        $this->validateBeforeStartGame();
        $cardDeck = $this->table->getCardDeck();

        try {
            $this->isStarted = true;

            $card = $this->cardService->pickFirstNoActionCard($cardDeck);
            $this->table->addPlayedCard($card);

            foreach ($this->table->getPlayers() as $player) {
                $player->takeCards($cardDeck, self::COUNT_START_PLAYED_CARDS);
            }
        } catch (\Exception $exception) {
            throw new GameException('The game needs help!', $exception);
        }
	}

	public function prepareCardDeck():Table
	{
		$cardCollection = $this->cardService->createDeck();
		$cardDeck = $this->cardService->shuffle($cardCollection);
		$this->table->addCardCollectionToDeck($cardDeck);

		return $this->getTable();
	}

    private function validateBeforeStartGame(): void
    {
        if ($this->table->getCardDeck()->count() === 0) {
            throw new GameException('Prepare card deck before game start');
        }

        if ($this->table->countPlayers() < self::MINIMAL_PLAYERS) {
            throw new GameException(sprintf('You need minimum %d players to start game', self::MINIMAL_PLAYERS));
        }
    }

    public function playRound(): void
    {
        $player = $this->table->getCurrentPlayer();

        if(!$player->canPlayRound()) {
            $this->table->finishRound();
            return;
        }

        try {
            $card = $this->cardSelector->chooseCard(
                $player,
                $this->table->getPlayedCards()->getLastCard(),
                $this->table->getPlayedCardColor()
            );

            $this->table->addPlayedCard($card);
            $this->cardActiveService->afterCard($card);
        } catch (CardNotFoundException $exception) {
            $player->takeCards($this->table->getCardDeck());
            $this->table->finishRound();
        }

    }
}