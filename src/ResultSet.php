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

	/** @var null|array<string, mixed> */
	protected $current;

	/** @var int */
	protected $position = -1;

	public function __construct(PDOStatement $statement)
	{
		$this->statement = $statement;

		/** @psalm-suppress TypeDoesNotContainNull */
		if ($statement->errorCode() === null) {
			$statement->execute();
		}

		$this->next();
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
		$this->statement->closeCursor();
		$this->statement->execute();

		$count = 0;
		while (false !== $this->statement->fetch(PDO::FETCH_LAZY)) {
			$count++;
		}

		$this->position = -1;
		$this->current = null;

		return $count;
	}

	public function toArray(): array
	{
		/** @var array */
		return iterator_to_array($this);
	}

	public function rewind(): void
	{
		if ($this->position !== 0) {

			$this->position = -1;
			$this->statement->closeCursor();
			$this->statement->execute();
			$this->next();
		}
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
		$record = $this->statement->fetch(PDO::FETCH_ASSOC);

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
