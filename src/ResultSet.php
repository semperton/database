<?php

declare(strict_types=1);

namespace Semperton\Database;

use PDO;
use PDOStatement;

use function iterator_to_array;

final class ResultSet implements ResultSetInterface
{
	/** @var PDOStatement */
	protected $statement;

	/** @var null|false|array<string, mixed> */
	protected $current;

	/** @var null|int */
	protected $count;

	/** @var null|int */
	protected $position;

	public function __construct(PDOStatement $statement)
	{
		$this->statement = $statement;
	}

	/**
	 * @return null|array<string, mixed>
	 */
	public function first(): ?array
	{
		if ($this->position !== 0) {
			$this->rewind();
		}

		return $this->current();
	}

	/** @psalm-suppress UnusedForeachValue */
	public function count(): int
	{
		if ($this->count === null) {

			$this->count = 0;

			foreach ($this as $row) {
				$this->count++;
			}
		}

		return $this->count;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		/** @var array<string, mixed> */
		return iterator_to_array($this);
	}

	public function rewind(): void
	{
		$this->statement->execute();
		$this->current = $this->statement->fetch(PDO::FETCH_ASSOC);
		$this->position = 0;
	}

	public function key(): int
	{
		if ($this->position === null) {
			$this->rewind();
		}
		/** @var int */
		return $this->position;
	}

	/**
	 * @return null|array<string, mixed>
	 */
	public function current(): ?array
	{
		if ($this->current === null) {
			$this->rewind();
		}
		return $this->current !== false ? $this->current : null;
	}

	public function next(): void
	{
		$this->current();
		$this->current = $this->statement->fetch(PDO::FETCH_ASSOC);
		$this->position = (int)$this->position + 1;
	}

	public function valid(): bool
	{
		$this->current();
		return $this->current !== false;
	}
}
