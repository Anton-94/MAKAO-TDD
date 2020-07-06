<?php

namespace Tests\Makao\Service;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\GameException;
use Makao\Player;
use Makao\Service\CardActionService;
use Makao\Service\CardSelector\CardSelectorInterface;
use Makao\Service\CardService;
use Makao\Service\GameService;
use Makao\Table;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GameServiceTest extends TestCase
{
	/**
	 * @var GameService
	 */
	private GameService $gameServiceUnderTest;

	/**
	 * @var CardService|MockObject
	 */
	private $cardServiceMock;

    /**
     * @var CardActionService|MockObject
     */
    private $cardActiveServiceMock;

    /**
     * @var CardSelectorInterface|MockObject
     */
    private $cardSelectorMock;

    protected function setUp(): void
	{
	    $this->cardSelectorMock = $this->getMockForAbstractClass(CardSelectorInterface::class);
	    $this->cardActiveServiceMock = $this->createMock(CardActionService::class);
		$this->cardServiceMock = $this->createMock(CardService::class);
		$this->gameServiceUnderTest = new GameService(
		    new Table(),
            $this->cardServiceMock,
            $this->cardSelectorMock,
            $this->cardActiveServiceMock
        );
	}

	public function testShouldReturnFalseWhenGameIsNotStarted()
	{
	    //when
		$actual = $this->gameServiceUnderTest->isStarted();

	    //then
		$this->assertFalse($actual);
	}

	public function testShouldReturnTrueWhenGameIsStarted()
	{
	    //given
        $this->gameServiceUnderTest->getTable()->addCardCollectionToDeck(new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_KING),
            new Card(Card::COLOR_HEART, Card::VALUE_ACE),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_THREE),
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
            new Card(Card::COLOR_SPADE, Card::VALUE_JACK),
            new Card(Card::COLOR_SPADE, Card::VALUE_QUEEN),
            new Card(Card::COLOR_SPADE, Card::VALUE_KING),
            new Card(Card::COLOR_SPADE, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]));

        $this->gameServiceUnderTest->addPlayers([
            new Player('Tom'),
            new Player('Peter')
        ]);

	    //when
		$this->gameServiceUnderTest->startGame();

	    //then
		$this->assertTrue($this->gameServiceUnderTest->isStarted());
	}

	public function testShouldInitNewGameWithEmptyTable()
	{
	    //when
		$table = $this->gameServiceUnderTest->getTable();

	    //then
		$this->assertSame(0, $table->countPlayers());
		$this->assertCount(0, $table->getCardDeck());
		$this->assertCount(0, $table->getPlayedCards());
	}
	
	public function testShouldAddPlayersToTheTable()
	{
	    //given
	 	$players = [
	 		new Player('Andy'),
	 		new Player('Rom'),
		];
	
	    //when
		$actual = $this->gameServiceUnderTest->addPlayers($players)->getTable();
	    
	    //then
		$this->assertSame(2, $actual->countPlayers());
	    
	}
	
	public function testShouldCreateShuffledCardDeck()
	{
		//given
		$cardCollection = new CardCollection([
			new Card(Card::COLOR_DIAMOND, Card::VALUE_ACE),
			new Card(Card::COLOR_HEART, Card::VALUE_FOUR)
		]);

		$shaffledCardCollection = new CardCollection([
			new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_ACE),
		]);

	    $this->cardServiceMock->expects($this->once())
			->method('createDeck')
			->willReturn($cardCollection);

	    $this->cardServiceMock->expects($this->once())
			->method('shuffle')
			->with($cardCollection)
			->willReturn($shaffledCardCollection);

	    //when
	    $table = $this->gameServiceUnderTest->prepareCardDeck();
	    
	    //then
		$this->assertCount(2, $table->getCardDeck());
		$this->assertCount(0, $table->getPlayedCards());
		$this->assertEquals($shaffledCardCollection, $table->getCardDeck());
	}
	
	public function testShouldThrowGameExceptionWhenStartGameWithoutCardDeck()
	{
	    //expect
        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Prepare card deck before game start');

	    //when
	    $this->gameServiceUnderTest->startGame();
	}

	public function testShouldThrowGameExceptionWhenStartGameWithoutMinimumPlayers()
	{
	    //given
        $this->gameServiceUnderTest->getTable()->addCardCollectionToDeck(new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE)
        ]));

	    //expect
        $this->expectException(GameException::class);
        $this->expectExceptionMessage('You need minimum 2 players to start game');

	    //when
	    $this->gameServiceUnderTest->startGame();
	}
	
	public function testShouldChooseNoActionCardAsFirstPlayedCardWhenStartGame()
	{
	    //given
	    $table = $this->gameServiceUnderTest->getTable();
	    $noActionCard = new Card(Card::COLOR_CLUB, Card::VALUE_ACE);
	    $cardCollection = new CardCollection([
            new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
            $noActionCard,
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_KING),
            new Card(Card::COLOR_HEART, Card::VALUE_ACE),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_THREE),
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
            new Card(Card::COLOR_SPADE, Card::VALUE_JACK),
            new Card(Card::COLOR_SPADE, Card::VALUE_QUEEN),
            new Card(Card::COLOR_SPADE, Card::VALUE_KING),
            new Card(Card::COLOR_SPADE, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]);

        $this->gameServiceUnderTest->addPlayers([
            new Player('Andy'),
            new Player('Rom'),
        ]);

	    $table->addCardCollectionToDeck($cardCollection);

	    $this->cardServiceMock->expects($this->once())
            ->method('pickFirstNoActionCard')
            ->with($cardCollection)
            ->willReturn($noActionCard);

	    //when
	    $this->gameServiceUnderTest->startGame();
	    
	    //then
	    $this->assertCount(1, $table->getPlayedCards());
	    $this->assertSame($noActionCard, $table->getPlayedCards()->pickCard());
	}
	
	public function testShouldThrowGameExceptionWhenCardServiceThrowException()
	{
	    //expect
	    $notFoundException = new CardNotFoundException('No regular cards in collection');
	    $gameException = new GameException('The game needs help!', $notFoundException);
	    
	    $this->expectExceptionObject($gameException);
	    $this->expectExceptionMessage('The game needs help! Issue: No regular cards in collection');

	    //given
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
        ]);

        $table = $this->gameServiceUnderTest->getTable();
        $table->addCardCollectionToDeck($cardCollection);

        $this->gameServiceUnderTest->addPlayers([
            new Player('Andy'),
            new Player('Rom'),
        ]);

        $this->cardServiceMock->expects($this->once())
            ->method('pickFirstNoActionCard')
            ->with($cardCollection)
            ->willThrowException($notFoundException);

	    //when
        $this->gameServiceUnderTest->startGame();
	}
	
	public function testShouldPlayersTakesFiveCardsFromDeckOnStartGame()
	{
	    //given
        $this->gameServiceUnderTest->addPlayers([
            new Player('Andy'),
            new Player('Rom'),
        ]);
        $noActionCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE);
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_KING),
            new Card(Card::COLOR_HEART, Card::VALUE_ACE),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_THREE),
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
            new Card(Card::COLOR_SPADE, Card::VALUE_JACK),
            new Card(Card::COLOR_SPADE, Card::VALUE_QUEEN),
            new Card(Card::COLOR_SPADE, Card::VALUE_KING),
            new Card(Card::COLOR_SPADE, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            $noActionCard
        ]);

        $table = $this->gameServiceUnderTest->getTable();
        $table->addCardCollectionToDeck($cardCollection);

        $this->cardServiceMock->expects($this->once())
            ->method('pickFirstNoActionCard')
            ->with($cardCollection)
            ->willReturn($noActionCard);

        //when
	    $this->gameServiceUnderTest->startGame();
	    
	    //then
        foreach ($table->getPlayers() as $player) {
            $this->assertCount(5, $player->getCards());
	    }
	}
	
	public function testShouldChooseCardToPlayFromPlayerCardsAndPutItOnTheTable()
	{
	    //given
	    $correctCard = new Card(Card::COLOR_CLUB, Card::VALUE_FIVE);

	    $player1 = new Player('Tom', new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            $correctCard
        ]));
	    $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);
	    $table = $this->gameServiceUnderTest->getTable();
	    $playedCard = new Card(Card::COLOR_CLUB, Card::VALUE_SIX);
	    $table->addPlayedCard($playedCard);

	    $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
	    ]);

	    $table->addCardCollectionToDeck($cardCollection);
        $this->cardSelectorMock->expects($this->once())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor())
            ->willReturn($correctCard);

        $this->cardActiveServiceMock->expects($this->once())
            ->method('afterCard')
            ->with($correctCard);

	    //when
	    $this->gameServiceUnderTest->playRound();
	    
	    //then
	    $this->assertSame($correctCard, $table->getPlayedCards()->getLastCard());
	}

	public function testShouldGivePlayerOneCardWhenHeHasNoCorrectCardToPlay()
	{
	    //given
	    $player1 = new Player('Tom', new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_SEVEN),
        ]));
	    $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);
	    $table = $this->gameServiceUnderTest->getTable();
	    $playedCard = new Card(Card::COLOR_CLUB, Card::VALUE_SIX);
	    $table->addPlayedCard($playedCard);

	    $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
	    ]);

	    $table->addCardCollectionToDeck($cardCollection);
        $this->cardSelectorMock->expects($this->once())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor())
            ->willThrowException(new CardNotFoundException());

        $this->cardActiveServiceMock->expects($this->never())
            ->method('afterCard');

	    //when
	    $this->gameServiceUnderTest->playRound();

	    //then
	    $this->assertSame($playedCard, $table->getPlayedCards()->getLastCard());
	    $this->assertCount(3 ,$player1->getCards());
	    $this->assertCount(3 ,$table->getCardDeck());
	    $this->assertSame($player2, $table->getCurrentPlayer());
	}

	public function testShouldSkipPlayerRoundWhenHeCanNotPlayRound()
	{
	    //given
	    $player1 = new Player('Tom', new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_SEVEN),
        ]));
	    $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);
	    $table = $this->gameServiceUnderTest->getTable();
	    $playedCard = new Card(Card::COLOR_CLUB, Card::VALUE_SIX);
	    $table->addPlayedCard($playedCard);

	    $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
	    ]);

	    $table->addCardCollectionToDeck($cardCollection);
        $this->cardSelectorMock->expects($this->never())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor());

        $this->cardActiveServiceMock->expects($this->never())
            ->method('afterCard');

        $player1->addRoundToSkip(2);
	    //when
	    $this->gameServiceUnderTest->playRound();

	    //then
	    $this->assertSame($playedCard, $table->getPlayedCards()->getLastCard());
	    $this->assertCount(2 ,$player1->getCards());
	    $this->assertCount(4 ,$table->getCardDeck());
	    $this->assertSame($player2, $table->getCurrentPlayer());
	}
}