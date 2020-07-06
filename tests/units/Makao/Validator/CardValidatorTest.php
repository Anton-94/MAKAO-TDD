<?php

namespace Tests\Makao\Validator;

use Makao\Card;
use Makao\Exception\CardDuplicationException;
use Makao\Validator\Validator\CardValidator;
use PHPUnit\Framework\TestCase;

class CardValidatorTest extends TestCase
{
	/**
	 * @var CardValidator
	 */
	private CardValidator $cardValidator;

	protected function setUp(): void
	{
		$this->cardValidator = new CardValidator();
	}

	public function cardsProvider(): array
	{
		return [
			'Return True When Valid Cards With The Same Colors' => [
				new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
				new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
				true
			],
			'Return False When Valid Cards With Different Colors' => [
				new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
				new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
				false
			],
			'Return True When Valid Cards With The Same Values' => [
				new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
				new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR),
				true
			],
			'Return False When Valid Cards With Different Values' => [
				new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
				new Card(Card::COLOR_DIAMOND, Card::VALUE_KING),
				false
			],
			'Queens for all' => [
				new Card(Card::COLOR_HEART, Card::VALUE_TEN),
				new Card(Card::COLOR_DIAMOND, Card::VALUE_QUEEN),
				true
			],
			'All for Queens' => [
				new Card(Card::COLOR_DIAMOND, Card::VALUE_QUEEN),
				new Card(Card::COLOR_HEART, Card::VALUE_TEN),
				true
			],
		];
	}

	/**
	 * @dataProvider cardsProvider
	 */
	public function testShouldValidCards(Card $actualCard, Card $newCard, $expected)
	{
	    //when
	    $actual = $this->cardValidator->valid($actualCard, $newCard);
	    
	    //then
	    $this->assertSame($expected, $actual);
	}
	
	public function testShouldThrowCardDuplicationExceptionWhenValidCardsAreTheSame()
	{
	    //expect
	    $this->expectException(CardDuplicationException::class);
	    $this->expectExceptionMessage('Valid card get the same cards: 5 spade');
	    //given
	    $card = new Card(Card::COLOR_SPADE, Card::VALUE_FIVE);
	
	    //when
	    $this->cardValidator->valid($card, $card);
	}
}