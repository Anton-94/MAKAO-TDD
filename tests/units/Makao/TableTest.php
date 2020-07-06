<?php

namespace Tests\Makao;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\TooManyPlayersAtTheTableException;
use Makao\Player;
use Makao\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
	/**
	 * @var Table
	 */
	private Table $tableUnderTest;

	public function setUp(): void
	{
		$this->tableUnderTest = new Table();
	}

	public function testShouldCreateEmptyTable()
	{
		//given
		$expected = 0;
		//when
		$actual = $this->tableUnderTest->countPlayers();
		//then
		$this->assertSame($expected, $actual);
	}

	public function testShouldAddOnePlayerToTable()
	{
	    //given
		$expected = 1;
		$player = new Player('User1');

	    //when
		$this->tableUnderTest->addPlayer($player);
		$actual = $this->tableUnderTest->countPlayers();

	    //then
		$this->assertSame($expected, $actual);
	}
	
	public function testShouldReturnCountWhenIAddManyPlayers()
	{
		//given
		$expected = 2;

		//when
		$this->tableUnderTest->addPlayer(new Player('User1'));
		$this->tableUnderTest->addPlayer(new Player('User2'));
		$actual = $this->tableUnderTest->countPlayers();

		//then
		$this->assertSame($expected, $actual);
	}
	
	public function testShouldThrowTooManyPlayersAtTheTableExceptionWhenITryAddMoreThanFourPlayers()
	{
	    //expect
		$this->expectException(TooManyPlayersAtTheTableException::class);
		$this->expectExceptionMessage('too many');

		//when
		$this->tableUnderTest->addPlayer(new Player("User1"));
		$this->tableUnderTest->addPlayer(new Player('User2'));
		$this->tableUnderTest->addPlayer(new Player('User3'));
		$this->tableUnderTest->addPlayer(new Player('User4'));
		$this->tableUnderTest->addPlayer(new Player('User5'));
	}
	
	public function testShouldReturnEmptyCardCollectionForPlayedCard()
	{
	    //when
		$actual = $this->tableUnderTest->getPlayedCards();

		//then
		$this->assertInstanceOf(CardCollection::class, $actual);
		$this->assertCount(0, $actual);
	}
	
	public function testShouldPutCardDeckOnTable()
	{
	    //given
		$cardCollection = new CardCollection([
			new Card(Card::COLOR_HEART, Card::VALUE_JACK)
		]);
	
	    //when
	    $table = new Table($cardCollection);
		$actual = $table->getCardDeck();

	    //then
		$this->assertSame($cardCollection, $actual);
	}
	
	public function testShouldAddCardCollectionToCardDeckOnTable()
	{
	    //given
	 	$cardCollection = new CardCollection([
			new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_ACE),
		]);
	
	    //when
		$actual = $this->tableUnderTest->addCardCollectionToDeck($cardCollection);
	    
	    //then
		$this->assertEquals($cardCollection, $actual->getCardDeck());
	}

	public function testShouldReturnCurrentPlayer()
	{
	    //given
	 	$player1 = new Player('Andy');
	 	$player2 = new Player('Tom');
	 	$player3 = new Player('Marta');

	 	$this->tableUnderTest->addPlayer($player1);
	 	$this->tableUnderTest->addPlayer($player2);
	 	$this->tableUnderTest->addPlayer($player3);

	    //when
	    $actual = $this->tableUnderTest->getCurrentPlayer();

	    //then
	    $this->assertSame($actual, $player1);
	}

	public function testShouldReturnNextPlayer()
	{
	    //given
	 	$player1 = new Player('Andy');
	 	$player2 = new Player('Tom');
	 	$player3 = new Player('Marta');

	 	$this->tableUnderTest->addPlayer($player1);
	 	$this->tableUnderTest->addPlayer($player2);
	 	$this->tableUnderTest->addPlayer($player3);

	    //when
	    $actual = $this->tableUnderTest->getNextPlayer();

	    //then
	    $this->assertSame($actual, $player2);
	}

	public function testShouldReturnPreviousPlayer()
	{
	    //given
	 	$player1 = new Player('Andy');
	 	$player2 = new Player('Tom');
	 	$player3 = new Player('Marta');
	 	$player4 = new Player('Morda');

	 	$this->tableUnderTest->addPlayer($player1);
	 	$this->tableUnderTest->addPlayer($player2);
	 	$this->tableUnderTest->addPlayer($player3);
	 	$this->tableUnderTest->addPlayer($player4);

	    //when
	    $actual = $this->tableUnderTest->getPreviousPlayer();

	    //then
	    $this->assertSame($actual, $player4);
	}
	
	public function testShouldSwitchCurrentPlayerWhenRoundFinished()
	{
		//given
		$player1 = new Player('Andy');
		$player2 = new Player('Tom');
		$player3 = new Player('Marta');

		$this->tableUnderTest->addPlayer($player1);
		$this->tableUnderTest->addPlayer($player2);
		$this->tableUnderTest->addPlayer($player3);

		//when & Then
		$this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
		$this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
		$this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());

		$this->tableUnderTest->finishRound();

		$this->assertSame($player2, $this->tableUnderTest->getCurrentPlayer());
		$this->assertSame($player3, $this->tableUnderTest->getNextPlayer());
		$this->assertSame($player1, $this->tableUnderTest->getPreviousPlayer());

		$this->tableUnderTest->finishRound();

		$this->assertSame($player3, $this->tableUnderTest->getCurrentPlayer());
		$this->assertSame($player1, $this->tableUnderTest->getNextPlayer());
		$this->assertSame($player2, $this->tableUnderTest->getPreviousPlayer());

		$this->tableUnderTest->finishRound();

		$this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
		$this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
		$this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());
	}

	public function testShouldAllowBackRoundOnTable()
	{
		//given
		$player1 = new Player('Andy');
		$player2 = new Player('Tom');
		$player3 = new Player('Marta');

		$this->tableUnderTest->addPlayer($player1);
		$this->tableUnderTest->addPlayer($player2);
		$this->tableUnderTest->addPlayer($player3);

		//when & Then
		$this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
		$this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
		$this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());

		$this->tableUnderTest->finishRound();

		$this->assertSame($player2, $this->tableUnderTest->getCurrentPlayer());
		$this->assertSame($player3, $this->tableUnderTest->getNextPlayer());
		$this->assertSame($player1, $this->tableUnderTest->getPreviousPlayer());

		$this->tableUnderTest->backRound();

        $this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());
	}
	
	public function testShouldThrowNewCardNotFoundExceptionWhenGetPlayedCardColorOnEmptyTable()
	{
	    //expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('No played card on the table yet!');

	    //when
        $this->tableUnderTest->getPlayedCardColor();
	}
	
	public function testShouldReturnPlayedCardColorSetByAddPlayedCard()
	{
	    //when
	    $this->tableUnderTest->addPlayedCard(new Card(Card::COLOR_CLUB, Card::VALUE_FIVE));
	    
	    //then
	    $this->assertEquals(Card::COLOR_CLUB, $this->tableUnderTest->getPlayedCardColor());
	}

	public function testShouldReturnPlayedCardColorSetByAddPlayedCards()
	{
	    $cardCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_FIVE),
            new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE)
        ]);

	    //when
	    $this->tableUnderTest->addPlayedCards($cardCollection);

	    //then
	    $this->assertEquals(Card::COLOR_DIAMOND, $this->tableUnderTest->getPlayedCardColor());
	}
}