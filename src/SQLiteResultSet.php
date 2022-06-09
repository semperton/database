<?php

declare(strict_types=1);

namespace Semperton\Database;

use SQLite3Result;

use function iterator_to_array;

use const SQLITE3_ASSOC;
use const SQLITE3_NUM;

final class SQLiteResultSet implements ResultSetInterface
{
	/** @var SQLite3Result */
	protected $result;

	/** @var null|array<string, mixed> */
	protected $current;

	/** @var int */
	protected $position = -1;

	public function __construct(SQLite3Result $result)
	{
		$this->result = $result;
		$this->next(); // fetch first value
	}

	/**
	 * @return null|array<string, mixed>
	 */
	public function first(): ?array
	{
		$this->rewind();

		return $this->current();
	}

	public function count(): int
	{
		$this->result->reset();

		$count = 0;
		while (false !== $this->result->fetchArray(SQLITE3_NUM)) {
			$count++;
		}

		$this->current = null;
		$this->position = -1;

		return $count;
	}

	public function toArray(): array
	{
		/** @var array<int, array<string, mixed>> */
		return iterator_to_array($this, false);
	}

	public function rewind(): void
	{
		$this->position = -1;
		$this->result->reset();
		$this->next();
	}

	public function key(): ?int
	{
		return $this->position >= 0 ? $this->position : null;
	}

	/**
	 * @return null|array<string, mixed>
	 */
	public function current(): ?array
	{
		return $this->current;
	}

	public function next(): void
	{
		/** @var false|array<string, mixed> */
		$record = $this->result->fetchArray(SQLITE3_ASSOC);

		if ($record !== false) {
			$this->current = $record;
			$this->position++;
		} else {
			$this->current = null;
			$this->position = -1;
		}
	}

	public function valid(): bool
	{
		return $this->current !== null;
	}
}
