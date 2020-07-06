<?php

namespace Tests\integration\Makao\Service;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Player;
use Makao\Service\CardActionService;
use Makao\Table;
use PHPUnit\Framework\TestCase;

class CardActionServiceTest extends TestCase
{
	private Player $player1;
	private Player $player2;
	private Player $player3;
	private Table $table;
	private CardActionService $cardActionServiceUnderTest;

	protected function setUp(): void
	{
		$playedCards = new CardCollection([
			new Card(Card::COLOR_SPADE, Card::VALUE_FIVE),
		]);

		$deck = new CardCollection([
			new Card(Card::COLOR_SPADE, Card::VALUE_FIVE),
			new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
			new Card(Card::COLOR_CLUB, Card::VALUE_FIVE),
			new Card(Card::COLOR_SPADE, Card::VALUE_SIX),
			new Card(Card::COLOR_HEART, Card::VALUE_SIX),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_SIX),
			new Card(Card::COLOR_CLUB, Card::VALUE_SIX),
			new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN),
			new Card(Card::COLOR_HEART, Card::VALUE_SEVEN),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_SEVEN),
			new Card(Card::COLOR_CLUB, Card::VALUE_SEVEN),
			new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT),
			new Card(Card::COLOR_HEART, Card::VALUE_EIGHT),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_EIGHT),
			new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT),
			new Card(Card::COLOR_SPADE, Card::VALUE_NINE),
			new Card(Card::COLOR_HEART, Card::VALUE_NINE),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_NINE),
			new Card(Card::COLOR_CLUB, Card::VALUE_NINE),
			new Card(Card::COLOR_SPADE, Card::VALUE_TEN),
			new Card(Card::COLOR_HEART, Card::VALUE_TEN),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_TEN),
			new Card(Card::COLOR_CLUB, Card::VALUE_TEN),
		]);

		$this->player1 = new Player('Andy');
		$this->player2 = new Player('Tom');
		$this->player3 = new Player('Max');

		$this->table = new Table($deck, $playedCards);
		$this->table->addPlayer($this->player1);
		$this->table->addPlayer($this->player2);
		$this->table->addPlayer($this->player3);

		$this->cardActionServiceUnderTest = new CardActionService($this->table);
	}

	public function testShouldGiveNextPlayerTwoCardsWhenCardTwoWasDropped()
	{
		//given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_TWO);

		//when
		$this->cardActionServiceUnderTest->afterCard($card);
	    
	    //then
		$this->assertCount(2, $this->player2->getCards());
		$this->assertSame($this->player3, $this->table->getCurrentPlayer());
	}
	
	public function testShouldGiveThirdPlayerFourCardsWhenCardTwoWasDroppedAndSecondPlayerHasCardTwoToDefend()
	{
	    //given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_TWO);
		$this->player2->getCards()->add(
			new Card(Card::COLOR_HEART, Card::VALUE_TWO)
		);

		//when
		$this->cardActionServiceUnderTest->afterCard($card);

		//then
		$this->assertCount(0, $this->player2->getCards());
		$this->assertCount(4, $this->player3->getCards());
		$this->assertSame($this->player1, $this->table->getCurrentPlayer());
	}

	public function testShouldGiveFirstPlayerSixCardsWhenCardTwoWasDroppedAndSecondAndThirdPlayerHaveCardsTwoToDefend()
	{
	    //given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_TWO);
		$this->player2->getCards()->add(
			new Card(Card::COLOR_HEART, Card::VALUE_TWO)
		);
		$this->player3->getCards()->add(
			new Card(Card::COLOR_CLUB, Card::VALUE_TWO)
		);

		//when
		$this->cardActionServiceUnderTest->afterCard($card);

		//then
		$this->assertCount(0, $this->player2->getCards());
		$this->assertCount(0, $this->player3->getCards());
		$this->assertCount(6, $this->player1->getCards());
		$this->assertSame($this->player2, $this->table->getCurrentPlayer());
	}

	public function testShouldGiveSecondPlayerEightCardsWhenCardTwoWasDroppedAndAllPlayersHaveCardsTwoToDefend()
	{
	    //given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_TWO);
		$this->player1->getCards()->add(
			new Card(Card::COLOR_DIAMOND, Card::VALUE_TWO)
		);
		$this->player2->getCards()->add(
			new Card(Card::COLOR_HEART, Card::VALUE_TWO)
		);
		$this->player3->getCards()->add(
			new Card(Card::COLOR_CLUB, Card::VALUE_TWO)
		);

		//when
		$this->cardActionServiceUnderTest->afterCard($card);

		//then
		$this->assertCount(8, $this->player2->getCards());
		$this->assertCount(0, $this->player3->getCards());
		$this->assertCount(0, $this->player1->getCards());
		$this->assertSame($this->player3, $this->table->getCurrentPlayer());
	}

	public function testShouldGiveNextPlayerThreeCardsWhenCardThreeWasDropped()
	{
	    //given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_THREE);

	    //when
		$this->cardActionServiceUnderTest->afterCard($card);

	    //then
		$this->assertCount(3, $this->player2->getCards());
		$this->assertSame($this->player3, $this->table->getCurrentPlayer());
	}

	public function testShouldGiveThirdPlayerSixCardsWhenCardThreeWasDroppedAndSecondPlayerHasCardThreeToDefend()
	{
	    //given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_THREE);
		$this->player2->getCards()->add(
			new Card(Card::COLOR_DIAMOND, Card::VALUE_THREE)
		);

	    //when
		$this->cardActionServiceUnderTest->afterCard($card);

	    //then
		$this->assertCount(0, $this->player2->getCards());
		$this->assertCount(6, $this->player3->getCards());
		$this->assertSame($this->player1, $this->table->getCurrentPlayer());
	}

	public function testShouldGiveFirstPlayerNineCardsWhenCardThreeWasDroppedAndSecondAndThirdPlayerHaveCardsThreeToDefend()
	{
		//given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_THREE);
		$this->player2->getCards()->add(
			new Card(Card::COLOR_DIAMOND, Card::VALUE_THREE)
		);
		$this->player3->getCards()->add(
			new Card(Card::COLOR_HEART, Card::VALUE_THREE)
		);

		//when
		$this->cardActionServiceUnderTest->afterCard($card);

		//then
		$this->assertCount(9, $this->player1->getCards());
		$this->assertCount(0, $this->player2->getCards());
		$this->assertCount(0, $this->player3->getCards());
		$this->assertSame($this->player2, $this->table->getCurrentPlayer());
	}

	public function testShouldGiveSecondPlayerTwelveCardsWhenCardThreeWasDroppedAllPlayerHaveCardsThreeToDefend()
	{
		//given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_THREE);
		$this->player1->getCards()->add(
			new Card(Card::COLOR_DIAMOND, Card::VALUE_THREE)
		);
		$this->player2->getCards()->add(
			new Card(Card::COLOR_CLUB, Card::VALUE_THREE)
		);
		$this->player3->getCards()->add(
			new Card(Card::COLOR_HEART, Card::VALUE_THREE)
		);

		//when
		$this->cardActionServiceUnderTest->afterCard($card);

		//then
		$this->assertCount(0, $this->player1->getCards());
		$this->assertCount(12, $this->player2->getCards());
		$this->assertCount(0, $this->player3->getCards());
		$this->assertSame($this->player3, $this->table->getCurrentPlayer());
	}
	
	public function testShouldSkipRoundForNextPlayerWhenCardFourWasDropped()
	{
	    //given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_FOUR);

	    //when
		$this->cardActionServiceUnderTest->afterCard($card);
	    
	    //then
		$this->assertSame($this->player3, $this->table->getCurrentPlayer());
	}
	
	public function testShouldSkipManyRoundsForNextPlayerWhenCardFourWasDroppedAndNextPlayersHaveCardsFourToDefend()
	{
		//given
		$card = new Card(Card::COLOR_SPADE, Card::VALUE_FOUR);
		$this->player2->getCards()->add(
			new Card(Card::COLOR_CLUB, Card::VALUE_FOUR)
		);
		$this->player3->getCards()->add(
			new Card(Card::COLOR_HEART, Card::VALUE_FOUR)
		);

		//when
		$this->cardActionServiceUnderTest->afterCard($card);

		//then
		$this->assertSame($this->player2, $this->table->getCurrentPlayer());
		$this->assertEquals(2, $this->player1->getRoundToSkip());
		$this->assertFalse( $this->player1->canPlayRound());
		$this->assertTrue( $this->player2->canPlayRound());
		$this->assertTrue( $this->player3->canPlayRound());
	}
	
	public function testShouldRequestCardByValueWhenCardJackWasDroppedAndTakeCardForEachPlayerWhenTheyNotHaveRequestedCard()
	{
	    //given
	    $requestValue = Card::VALUE_SEVEN;
	    $card = new Card(Card::COLOR_SPADE, Card::VALUE_JACK);
	    $requestedCard = new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN);
        $this->player1->getCards()->add($requestedCard);

	    //when
        $this->cardActionServiceUnderTest->afterCard($card, $requestValue);

	    //then
        $this->assertCount(0, $this->player1->getCards());
        $this->assertCount(1, $this->player2->getCards());
        $this->assertCount(1, $this->player3->getCards());
        $this->assertSame($requestedCard, $this->table->getPlayedCards()->getLastCard());
        $this->assertSame($this->player2, $this->table->getCurrentPlayer());
	}

	public function testShouldRequestCardByValueWhenCardJackWasDroppedAndPickCardsForEachPlayerWhenTheyHaveRequestedCard()
	{
	    //given
	    $requestValue = Card::VALUE_SEVEN;
	    $card = new Card(Card::COLOR_SPADE, Card::VALUE_JACK);

        $this->player1->getCards()->add(new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN));
        $this->player2->getCards()->add(new Card(Card::COLOR_HEART, Card::VALUE_SEVEN));
        $this->player3->getCards()->add(new Card(Card::COLOR_CLUB, Card::VALUE_SEVEN));

	    //when
        $this->cardActionServiceUnderTest->afterCard($card, $requestValue);

	    //then
        $this->assertCount(0, $this->player1->getCards());
        $this->assertCount(0, $this->player2->getCards());
        $this->assertCount(0, $this->player3->getCards());
        $this->assertSame($this->player2, $this->table->getCurrentPlayer());
	}

	public function testShouldAllowDropManyRequestedCardsByValueWhenPlayerDropJackCard()
	{
	    //given
	    $requestValue = Card::VALUE_SEVEN;
	    $card = new Card(Card::COLOR_SPADE, Card::VALUE_JACK);

        $requestedCard = new Card(Card::COLOR_CLUB, Card::VALUE_SEVEN);
        $this->player1->getCards()->add(new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN));
        $this->player1->getCards()->add(new Card(Card::COLOR_HEART, Card::VALUE_SEVEN));
        $this->player1->getCards()->add($requestedCard);

	    //when
        $this->cardActionServiceUnderTest->afterCard($card, $requestValue);

	    //then
        $this->assertCount(0, $this->player1->getCards());
        $this->assertCount(1, $this->player2->getCards());
        $this->assertCount(1, $this->player3->getCards());
        $this->assertSame($requestedCard, $this->table->getPlayedCards()->getLastCard());
        $this->assertSame($this->player2, $this->table->getCurrentPlayer());
	}

    public function testShouldGiveNextPlayerFiveCardsWhenCardKingHeartWasDropped()
    {
        //given
        $card = new Card(Card::COLOR_HEART, Card::VALUE_KING);

        //when
        $this->cardActionServiceUnderTest->afterCard($card);

        //then
        $this->assertCount(5, $this->player2->getCards());
        $this->assertSame($this->player3, $this->table->getCurrentPlayer());
    }

    public function testShouldGivePreviousPlayerFiveCardsWhenCardKingSpadeWasDropped()
    {
        //given
        $card = new Card(Card::COLOR_SPADE, Card::VALUE_KING);

        //when
        $this->cardActionServiceUnderTest->afterCard($card);

        //then
        $this->assertCount(5, $this->player3->getCards());
        $this->assertSame($this->player1, $this->table->getCurrentPlayer());
    }

    public function testShouldGiveCurrentPlayerTenCardsWhenCardKingHeartWasDroppedAndNextPlayerHasKingSpadeToDefence()
    {
        //given
        $card = new Card(Card::COLOR_HEART, Card::VALUE_KING);
        $this->player2->getCards()->add(new Card(Card::VALUE_KING, Card::COLOR_SPADE));

        //when
        $this->cardActionServiceUnderTest->afterCard($card);

        //then
        $this->assertCount(10, $this->player1->getCards());
        $this->assertSame($this->player2, $this->table->getCurrentPlayer());
    }

    public function testShouldGiveCurrentPlayerTenCardsWhenCardKingSpadeWasDroppedAndPreviousPlayerHasKingHeartToDefence()
    {
        //given
        $card = new Card(Card::COLOR_SPADE, Card::VALUE_KING);
        $this->player3->getCards()->add(new Card(Card::VALUE_KING, Card::COLOR_HEART));

        //when
        $this->cardActionServiceUnderTest->afterCard($card);

        //then
        $this->assertCount(10, $this->player1->getCards());
        $this->assertSame($this->player2, $this->table->getCurrentPlayer());
    }
    
    public function testShouldNotRunAnyActionForOtherKings()
    {
        //given
        $card = new Card(Card::COLOR_DIAMOND, Card::VALUE_KING);

        //when
        $this->cardActionServiceUnderTest->afterCard($card);

        //then
        $this->assertCount(0, $this->player1->getCards());
        $this->assertCount(0, $this->player1->getCards());
        $this->assertCount(0, $this->player1->getCards());
        $this->assertSame($this->player2, $this->table->getCurrentPlayer());
    }

    public function testShouldNotRunAnyActionForAnyNoActionCard()
    {
        //given
        $card = new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE);

        //when
        $this->cardActionServiceUnderTest->afterCard($card);

        //then
        $this->assertCount(0, $this->player1->getCards());
        $this->assertCount(0, $this->player1->getCards());
        $this->assertCount(0, $this->player1->getCards());
        $this->assertSame($this->player2, $this->table->getCurrentPlayer());
    }
    
    public function testShouldChangeColorToPlayOnTableAfterCardAce()
    {
        //given
        $requestColor = Card::COLOR_HEART;
        $card = new Card(Card::COLOR_SPADE, Card::VALUE_ACE);
    
        //when & then
        $this->assertEquals(Card::COLOR_SPADE, $this->table->getPlayedCardColor());

        $this->cardActionServiceUnderTest->afterCard($card, $requestColor);

        $this->assertEquals(Card::COLOR_HEART, $this->table->getPlayedCardColor());
    }
}