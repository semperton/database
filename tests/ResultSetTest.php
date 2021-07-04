<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Semperton\Database\Connection;
use Semperton\Database\ResultSetInterface;

final class ResultSetTest extends TestCase
{
	public function testInstance(): void
	{
		$conn = new Connection('sqlite::memory:');
		$result = $conn->fetchAll('select');
		$this->assertNull($result);

		$result = $conn->fetchAll('select 1');
		$this->assertInstanceOf(ResultSetInterface::class, $result);
	}

	public function testFirst(): void
	{
		$conn = new Connection('sqlite::memory:');
		$result = $conn->fetchAll('values (42), (2), (1)');
		$first = $result->first();

		$this->assertSame('42', reset($first));
	}

	public function testCount(): void
	{
		$conn = new Connection('sqlite::memory:');
		$result = $conn->fetchAll('values (42), (2), (1), (3)');
		$count = $result->count();

		$this->assertSame(4, $count);
	}

	public function testIterator(): void
	{
		$conn = new Connection('sqlite::memory:');
		$result = $conn->fetchAll('values (1), (2), (3), (4), (5)');

		$this->assertEquals(0, $result->key());

		$result->next();
		$result->next();

		$this->assertEquals(2, $result->key());

		$result->next();
		$result->next();

		$this->assertEquals(4, $result->key());

		$this->assertTrue($result->valid());

		$result->next();

		$this->assertFalse($result->valid());
		$this->assertNull($result->current());

		$result->rewind();

		$this->assertEquals(0, $result->key());
	}
}
