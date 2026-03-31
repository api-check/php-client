<?php

namespace ApiCheck\Api\Tests\Exceptions;

use ApiCheck\Api\Tests\TestCase;
use ApiCheck\Api\Exceptions\NoExactMatchException;

class NoExactMatchExceptionTest extends TestCase
{
    public function testConstructorAcceptsNumberAdditions(): void
    {
        $numberAdditions = ['a', 'b', '1', '2'];
        $exception = new NoExactMatchException('No exact match found', 0, null, $numberAdditions);

        $this->assertInstanceOf(NoExactMatchException::class, $exception);
        $this->assertEquals('No exact match found', $exception->getMessage());
    }

    public function testGetNumberAdditionsReturnsArray(): void
    {
        $numberAdditions = ['a', 'b', '1', '2'];
        $exception = new NoExactMatchException('No exact match found', 0, null, $numberAdditions);

        $this->assertEquals($numberAdditions, $exception->getNumberAdditions());
        $this->assertIsArray($exception->getNumberAdditions());
    }

    public function testGetNumberAdditionsReturnsEmptyArrayByDefault(): void
    {
        $exception = new NoExactMatchException('No exact match found');

        $this->assertEquals([], $exception->getNumberAdditions());
        $this->assertIsArray($exception->getNumberAdditions());
    }

    public function testGetNumberAdditionsReturnsEmptyArrayWhenEmptyArrayProvided(): void
    {
        $exception = new NoExactMatchException('No exact match found', 0, null, []);

        $this->assertEquals([], $exception->getNumberAdditions());
    }

    public function testGetNumberAdditionsReturnsCorrectCount(): void
    {
        $numberAdditions = ['a', 'b', 'c'];
        $exception = new NoExactMatchException('No exact match found', 0, null, $numberAdditions);

        $this->assertCount(3, $exception->getNumberAdditions());
    }

    public function testConstructorWithAllParameters(): void
    {
        $numberAdditions = ['1', '2', '3'];
        $previous = new \Exception('Previous error');
        $exception = new NoExactMatchException(
            'Custom message',
            422,
            $previous,
            $numberAdditions
        );

        $this->assertEquals('Custom message', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals($numberAdditions, $exception->getNumberAdditions());
    }

    public function testExtendsException(): void
    {
        $exception = new NoExactMatchException('Test');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
