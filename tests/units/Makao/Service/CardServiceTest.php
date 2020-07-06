<?php

namespace Tests\Makao\Service;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Service\CardService;
use Makao\Service\ShuffleService;
use phpDocumentor\Reflection\Types\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CardServiceTest extends TestCase
{
	/**
	 * @var CardService
	 */
	private CardService $cardServiceUnderTest;
	/**
	 * @var ShuffleService|MockObject
	 */
	private $shuffleServiceMock;

	protected function setUp(): void
	{
		$this->shuffleServiceMock = $this->createMock(ShuffleService::class);
		$this->cardServiceUnderTest = new CardService($this->shuffleServiceMock);
	}

	public function testShouldAllowCreateNewCardCollection()
	{
	    //when
		$actual = $this->cardServiceUnderTest->createDeck();
	
	    //then
		$this->assertInstanceOf(CardCollection::class, $actual);
		$this->assertCount(52, $actual);

		$i = 0;
		foreach (Card::values() as $value) {
			foreach (Card::colors() as $color) {
				$this->assertEquals($value, $actual[$i]->getValue());
				$this->assertEquals($color, $actual[$i]->getColor());
				++$i;
			}
		}

		return $actual;
	}

	/**
	 * @depends testShouldAllowCreateNewCardCollection
	 */
	public function testShouldShuffleCardsInCardCollection(CardCollection $cardCollection)
	{
		$this->shuffleServiceMock->expects($this->once())
			->method('shuffle')
			->willReturn(array_reverse($cardCollection->toArray()));

		//when
		$actual = $this->cardServiceUnderTest->shuffle($cardCollection);

		//then
		$this->assertNotEquals($cardCollection, $actual);
		$this->assertEquals($cardCollection->pickCard(), $actual[51]);
	}
	
	public function testShouldPickFirstNoActionCardFromCollection()
	{
	    //given
	    $noActionCard = new Card(Card::COLOR_CLUB, Card::VALUE_FIVE);
	    $cardCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            $noActionCard
        ]);

	    //when
        $actual = $this->cardServiceUnderTest->pickFirstNoActionCard($cardCollection);
	    
	    //then
        $this->assertCount(7, $cardCollection);
        $this->assertSame($noActionCard, $actual);
	}

	public function testShouldThrowCardNotFoundExceptionWhenPickFirstNoActionCardFromCollectionWithOnlyActionCards()
	{
	    //expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('No regulars card in collection!');

	    //given
	    $cardCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]);

	    //when
        $this->cardServiceUnderTest->pickFirstNoActionCard($cardCollection);
	}

    public function testShouldPickFirstNoActionCardFromCollectionAndMovePreviousActionCardsOnTheEnd()
    {
        //given
        $noActionCard = new Card(Card::COLOR_CLUB, Card::VALUE_FIVE);
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            $noActionCard,
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]);

        $expectCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
        ]);

        //when
        $actual = $this->cardServiceUnderTest->pickFirstNoActionCard($cardCollection);

        //then
        $this->assertCount(7, $cardCollection);
        $this->assertSame($noActionCard, $actual);
        $this->assertEquals($expectCollection, $cardCollection);
    }
}