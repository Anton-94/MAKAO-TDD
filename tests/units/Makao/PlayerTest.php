<?php

namespace Tests\units\Makao;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Player;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
	const MAKAO = 'Makao';

	public function testShouldWritePlayerName()
	{
	    //given
		$player = new Player('Andy');
	    //when
		ob_start();
		echo $player;
		$actual = ob_get_clean();
	
	    //then
		$this->assertEquals('Andy', $actual);
	}
	
	public function testShouldReturnPlayerCardCollection()
	{
	    //given
		$cardCollection = new CardCollection([
			new Card(Card::COLOR_HEART, Card::VALUE_JACK),
		]);
		$player = new Player('Andy', $cardCollection);

	    //when
		$actual = $player->getCards();

		//then
		$this->assertSame($cardCollection, $actual);
	}
	
	public function testShouldAllowPlayerTakeCardFromDeck()
	{
	    //given
		$card = new Card(Card::COLOR_HEART, Card::VALUE_JACK);
		$cardCollection = new CardCollection([$card]);

		$player = new Player('Andy');
	    //when
		$actual = $player->takeCards($cardCollection)->getCards();
	    //then
		$this->assertCount(0, $cardCollection);
		$this->assertCount(1, $actual);
		$this->assertEquals($card, $actual[0]);
	}
	
	public function testShouldAllowPlayerTakeManyCardsFromCardCollection()
	{
		//given
		$card1 = new Card(Card::COLOR_SPADE, Card::VALUE_JACK);
		$card2 = new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR);
		$card3 = new Card(Card::COLOR_HEART, Card::VALUE_ACE);
		$cardCollection = new CardCollection([$card1, $card2, $card3]);

		$player = new Player('Andy');
		//when
		$actual = $player->takeCards($cardCollection, 2)->getCards();
		//then
		$this->assertCount(1, $cardCollection);
		$this->assertCount(2, $actual);

		$this->assertEquals($card1, $actual->pickCard());
		$this->assertEquals($card2, $actual->pickCard());
		$this->assertEquals($card3, $cardCollection->pickCard());
	}
	
	public function testShouldAllowPickChosenCardFromPlayerCardCollection()
	{
		$card1 = new Card(Card::COLOR_SPADE, Card::VALUE_JACK);
		$card2 = new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR);
		$card3 = new Card(Card::COLOR_HEART, Card::VALUE_ACE);
		$cardCollection = new CardCollection([$card1, $card2, $card3]);

	    //given
	 	$player = new Player('Andy', $cardCollection);
	
	    //when
	    $actual = $player->pickCard(2);
	    
	    //then
		$this->assertSame($actual, $card3);
	}

	public function testShouldAllowPlayerSaysMakao()
	{
		//given
		$player = new Player('Andy');

		//when
		$actual = $player->sayMakao();

		//then
		$this->assertEquals(self::MAKAO, $actual);
	}

    public function testShouldReturnFirstCardByPickCardByValueWhenPlayerHasMoreCorrectCard()
	{
	    //given
		$card = new Card(Card::COLOR_HEART, Card::VALUE_TWO);
		$player = new Player('Andy', new CardCollection([
			$card,
			new Card(Card::COLOR_SPADE, Card::VALUE_TWO)
		]));

		//when
		$actual = $player->pickCardByValue(Card::VALUE_TWO);

	    //then
	    $this->assertSame($card, $actual);
	}

    public function testShouldReturnTrueWhenPlayerCanPlayRound()
	{
	    //given
		$player = new Player('Andy');

	    //when
	    $actual = $player->canPlayRound();

	    //then
	    $this->assertTrue($actual);
	}

    public function testShouldReturnFalseWhenPlayerNotCanPlayRound()
	{
	    //given
		$player = new Player('Andy');

	    //when
		$player->addRoundToSkip();
	    $actual = $player->canPlayRound();

	    //then
	    $this->assertFalse($actual);
	}

    public function testShouldSkipManyRoundsAndBackToPlayAfter()
	{
		//given
		$player = new Player('Andy');

		//when & Then
		$this->assertTrue($player->canPlayRound());

		$player->addRoundToSkip(2);
		$this->assertFalse($player->canPlayRound());
		$this->assertSame(2, $player->getRoundToSkip());

		$player->skipRound();
		$this->assertFalse($player->canPlayRound());
		$this->assertSame(1, $player->getRoundToSkip());

		$player->skipRound();
		$this->assertTrue($player->canPlayRound());
		$this->assertSame(0, $player->getRoundToSkip());
	}

    public function testShouldThrowCardNotFoundExceptionWhenPlayerTryPickCardByValueAndHasNotCorrectCardInHand()
    {
        //expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has not card with value 2');

        //given
        $player = new Player('Andy');
        $player->pickCardByValue(Card::VALUE_TWO);
    }

    public function testShouldReturnPickCardByValueWhenPlayerHasCorrectCard()
    {
        //given
        $card = new Card(Card::COLOR_HEART, Card::VALUE_TWO);
        $player = new Player('Andy', new CardCollection([
            $card
        ]));

        //when
        $actual = $player->pickCardByValue(Card::VALUE_TWO);

        //then
        $this->assertSame($card, $actual);
    }

    public function testShouldThrowCardNotFoundExceptionWhenPlayerTryPickCardsByValueAndHasNotCorrectCardInHand()
    {
        //expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has not cards with value 2');

        //given
        $player = new Player('Andy');
        $player->pickCardsByValue(Card::VALUE_TWO);
    }

    public function testShouldReturnPickCardsByValueWhenPlayerHasCorrectCard()
    {
        //given
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO)
        ]);
        $player = new Player('Andy', clone $cardCollection);

        //when
        $actual = $player->pickCardsByValue(Card::VALUE_TWO);

        //then
        $this->assertEquals($cardCollection, $actual);
    }

    public function testShouldReturnFirstCardByPickCardsByValueWhenPlayerHasMoreCorrectCard()
    {
        //given
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
        ]);
        $player = new Player('Andy', clone $cardCollection);

        //when
        $actual = $player->pickCardsByValue(Card::VALUE_TWO);

        //then
        $this->assertEquals($cardCollection, $actual);
    }

    public function testShouldThrowCardNotFoundExceptionWhenPlayerTryPickCardByValueAndColorAndHasNotCorrectCardInHand()
    {
        //expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has not card with value 2 and color heart');

        //given
        $player = new Player('Andy');
        $player->pickCardByValueAndColor(Card::VALUE_TWO, Card::COLOR_HEART);
    }

    public function testShouldReturnPickCardByValueAndColorWhenPlayerHasCorrectCard()
    {
        //given
        $card = new Card(Card::COLOR_HEART, Card::VALUE_TWO);
        $player = new Player('Andy', new CardCollection([
            $card
        ]));

        //when
        $actual = $player->pickCardByValueAndColor(Card::VALUE_TWO, Card::COLOR_HEART);

        //then
        $this->assertSame($card, $actual);
    }
}