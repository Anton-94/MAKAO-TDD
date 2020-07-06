<?php

namespace Tests\Makao\Collection;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\MethodNotAllowedException;
use PHPUnit\Framework\TestCase;

class CardCollectionTest extends TestCase
{
	private CardCollection $cardCollection;

	protected function setUp(): void
	{
		$this->cardCollection = new CardCollection();
	}

	public function testShouldReturnZeroOnEmptyCollection()
	{
	    //then
		$this->assertCount(0, $this->cardCollection);
	}
	
	public function testShouldAddNewCardToCardCollection()
	{
	    //given
		$card = new Card(Card::COLOR_CLUB, Card::VALUE_ACE);
	    //when
		$this->cardCollection->add($card);
	    //then
		$this->assertCount(1, $this->cardCollection);
	}
	
	public function testShouldAddNewCardsInChainToCardCollection()
	{
		//given
		$card1 = new Card(Card::COLOR_CLUB, Card::VALUE_ACE);
		$card2 = new Card(Card::COLOR_SPADE, Card::VALUE_JACK);

		//when
		$this->cardCollection
			->add($card1)
			->add($card2);

		//then
		$this->assertCount(2, $this->cardCollection);
	}
	
	public function testShouldThrowCardNotFoundExceptionWhenITryPickCardFromEmptyCardCollection()
	{
	    //expect
	    $this->expectException(CardNotFoundException::class);

	    //when
		$this->cardCollection->pickCard();
	}
	
	public function testShouldIterableOnCardCollection()
	{
	    //given
		$card = new Card(Card::COLOR_CLUB, Card::VALUE_ACE);
	    //when & Then
		$this->cardCollection->add($card);

		$this->assertTrue($this->cardCollection->valid());
		$this->assertSame($card, $this->cardCollection->current());
		$this->assertSame(0, $this->cardCollection->key());

		$this->cardCollection->next();
		$this->assertFalse($this->cardCollection->valid());
		$this->assertSame(1, $this->cardCollection->key());

		$this->cardCollection->rewind();
		$this->assertTrue($this->cardCollection->valid());
		$this->assertSame(0, $this->cardCollection->key());
	}
	
	public function testShouldGetFirstCardFromCardCollectionAndRemoveThisCardFromDeck()
	{
		//given
		$card1 = new Card(Card::COLOR_SPADE, Card::VALUE_FOUR);
		$card2 = new Card(Card::COLOR_HEART, Card::VALUE_FIVE);
		$this->cardCollection
			->add($card1)
			->add($card2);

		//when
		$actual = $this->cardCollection->pickCard();

		//then
		$this->assertCount(1, $this->cardCollection);
		$this->assertSame($card1, $actual);
		$this->assertSame($card2, $this->cardCollection[0]);
	}

	public function testShouldThrowCardNotFoundExceptionWhenIPickedAllCardFromCardCollection()
	{
		//expect
		$this->expectException(CardNotFoundException::class);
		$this->expectExceptionMessage('You can not pick card from empty CardCollection!');

		//given
		$card1 = new Card(Card::COLOR_HEART, Card::VALUE_KING);
		$card2 = new Card(Card::COLOR_CLUB, Card::VALUE_ACE);
		$this->cardCollection
			->add($card1)
			->add($card2);

		//when
		$actual = $this->cardCollection->pickCard();
		$this->assertSame($card1, $actual);

		$actual = $this->cardCollection->pickCard();
		$this->assertSame($card2, $actual);

		$this->cardCollection->pickCard();
	}
	
	public function testShouldReturnChosenCardPickedFromCollection()
	{
	    //given
		$card1 = new Card(Card::COLOR_HEART, Card::VALUE_KING);
		$card2 = new Card(Card::COLOR_CLUB, Card::VALUE_ACE);
		$this->cardCollection
			->add($card1)
			->add($card2);

	    //when
		$actual = $this->cardCollection->pickCard(1);
		$actual2 = $this->cardCollection->pickCard(0);

	    //then
		$this->assertSame($actual, $card2);
		$this->assertSame($actual2, $card1);

	}
	
	public function testShouldThrowMethodNotAllowedExceptionWhenYouTryAddCardToCollectionAsArray()
	{
	    //expect
		$this->expectException(MethodNotAllowedException::class);
	
	    //given
		$card = new Card(Card::COLOR_CLUB, Card::VALUE_ACE);
	
	    //when
		$this->cardCollection[] = $card;
	}

	public function testShouldReturnCardCollectionAsArray()
	{
		//given
		$cards = [
			new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR),
		];
		//when
		$actual = new CardCollection($cards);

		//then
		$this->assertEquals($cards, $actual->toArray());
	}
	
	public function testShouldAddCardCollectionToCardCollection()
	{
	    //given
	    $cardCollection = new CardCollection([
			new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
			new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR),
		]);
	
	    //when
		$actual = $this->cardCollection->addCollection($cardCollection);

	    //then
	    $this->assertEquals($cardCollection, $actual);
	}
	
	public function testShouldReturnLastCardFromCollectionWithoutPicking()
	{
	    //given
        $lastCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR);
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            $lastCard
        ]);
	
	    //when
	    $actual = $cardCollection->getLastCard();
	    
	    //then
	    $this->assertSame($actual, $lastCard);
	    $this->assertCount(2, $cardCollection);
	}

    public function testShouldThrowCardNotFoundExceptionWhenTryGetLastCardFromEmptyCollection()
    {
        //expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('You can not get last card from empty CardCollection!');

        //when
        $this->cardCollection->getLastCard();
    }
}