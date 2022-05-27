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

	/** @var array */
	protected $params;

	/** @var bool */
	protected $executed;

	/** @var null|array<string, mixed> */
	protected $current;

	/** @var int */
	protected $position = -1;

	public function __construct(PDOStatement $statement, array $params)
	{
		$this->statement = $statement;
		$this->params = $params;

		/** @psalm-suppress RedundantCondition */
		$this->executed = $this->statement->errorCode() !== null;
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
		$this->position = -1;
		$this->current = null;

		$this->execute();

		$count = 0;
		while (false !== $this->statement->fetch(PDO::FETCH_LAZY)) {
			$count++;
		}

		return $count;
	}

	public function toArray(): array
	{
		/** @var array */
		return iterator_to_array($this, false);
	}

	public function rewind(): void
	{
		$this->position = -1;
		$this->execute();
		$this->next();
	}

	public function key(): ?int
	{
		if (!$this->executed) {
			$this->rewind();
		}

		return $this->position >= 0 ? $this->position : null;
	}

	/**
	 * @return null|array<string, mixed>
	 */
	public function current(): ?array
	{
		if (!$this->executed) {
			$this->rewind();
		}

		return $this->current;
	}

	public function next(): void
	{
		if (!$this->executed) {
			$this->rewind();
		}

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
		if (!$this->executed) {
			$this->rewind();
		}

		return $this->current !== null;
	}

	protected function execute(): void
	{
		$this->statement->closeCursor();
		$this->statement->execute($this->params);
		$this->executed = true;
	}
}
