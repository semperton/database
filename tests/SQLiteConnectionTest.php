<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Semperton\Database\SQLiteConnection;
use Semperton\Database\SQLiteResultSet;

final class SQLiteConnectionTest extends TestCase
{
	public function testException(): void
	{
		$this->expectException(Exception::class);
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('select');
	}

	public function testExecute(): void
	{
		$this->expectException(Exception::class);

		$conn = new SQLiteConnection(':memory:');
		$result = $conn->execute('create table test (id integer primary key)');
		$this->assertTrue($result);

		$result = $conn->execute('create table test (id integer primary key)');
		$this->assertFalse($result);
	}

	public function testFetchValue(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('create table test (id integer primary key, number integer not null, text varchar not null)');

		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$number = $conn->fetchValue('select number from test');
		$this->assertEquals(42, (int)$number);
		$text = $conn->fetchValue('select text from test');
		$this->assertEquals('hello', $text);
	}

	public function testFetchRow(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('create table test (id integer primary key, number integer not null, text varchar not null)');

		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$conn->execute('insert into test (number, text) values (?, ?)', [55, 'world']);

		$row = $conn->fetchRow('select number, text from test where id = :id', [':id' => 2]);
		$this->assertSame(['number' => 55, 'text' => 'world'], $row);
	}

	public function testFetchAll(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('create table test (id integer primary key, number integer not null, text varchar not null)');

		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$conn->execute('insert into test (number, text) values (?, ?)', [55, 'world']);

		$rows = $conn->fetchAll('select number from test where id');

		$number = 42;
		foreach ($rows as $row) {
			$this->assertNotEmpty($row['number']);
			$this->assertEquals($number, $row['number']);
			$number = 55;
		}

		$this->assertEquals(55, $number);
	}

	public function testFetchResult(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('create table test (id integer primary key, number integer not null, text varchar not null)');

		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$conn->execute('insert into test (number, text) values (?, ?)', [55, 'world']);

		$rows = $conn->fetchResult('select number, text from test');

		$this->assertInstanceOf(SQLiteResultSet::class, $rows);

		$expected = [
			['number' => 42, 'text' => 'hello'],
			['number' => 55, 'text' => 'world']
		];

		$this->assertSame($expected, $rows->toArray());
	}

	public function testLastInsertId(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('create table test (id integer primary key, number integer not null, text varchar not null)');

		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$this->assertEquals(3, $conn->lastInsertId());
	}

	public function testAffectedRows(): void
	{
		$conn = new SQLiteConnection(':memory:');
		$conn->execute('create table test (id integer primary key, number integer not null, text varchar not null)');

		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'hello']);
		$conn->execute('insert into test (number, text) values (?, ?)', [42, 'world']);

		$conn->execute('update test set text = "update" where number = ?', [42]);

		$affected = $conn->affectedRows();
		$this->assertEquals(2, $affected);
	}
}
