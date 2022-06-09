<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Semperton\Database\EmptyResultSet;
use Semperton\Database\SQLiteConnection;
use Semperton\Database\SQLiteResultSet;

final class SQLiteResultSetTest extends TestCase
{
	public function testInstance(): void
	{
		$this->expectException(Exception::class);

		$conn = new SQLiteConnection(':memory:');
		$result = $conn->fetchResult('select');
		$this->assertInstanceOf(EmptyResultSet::class, $result);

		$result = $conn->fetchResult('select 1');
		$this->assertInstanceOf(SQLiteResultSet::class, $result);
	}

	public function testFirst(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$result = $conn->fetchResult('values (42), (2), (1)');
		$first = $result->first() ?? [];

		$this->assertSame(42, reset($first));
	}

	public function testCount(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$result = $conn->fetchResult('values (42), (2), (1), (3)');
		$count = $result->count();

		// end of iterator
		$this->assertNull($result->current());
		$this->assertSame(4, $count);

		$result = $conn->fetchResult('values (42), (2)');
		$this->assertEquals(2, count($result));

		$this->assertNull($result->current());
	}

	public function testToArray(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$result = $conn->fetchResult('values (42), (2), (1)');
		$arr = $result->toArray();
		$this->assertIsArray($arr);

		$this->assertSame([0, 1, 2], array_keys($arr));
	}

	public function testIterator(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$result = $conn->fetchResult('values (1), (2), (3), (4), (5)');

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

	public function testResultMutation(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('create table test (id integer not null primary key, number integer not null, text varchar not null)');

		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$conn->execute('insert into test (number, text) values (?, ?)', [55, 'world']);
		$result = $conn->fetchResult('select * from test');

		$this->assertTrue($result->valid());

		$this->assertEquals(2, count($result));

		$conn->execute('insert into test (number, text) values (?, ?)', [77, 'between']);

		$this->assertEquals(3, count($result));

		$conn->execute('delete from test where number < ?', [77]);

		$this->assertEquals(1, count($result));
	}
}
