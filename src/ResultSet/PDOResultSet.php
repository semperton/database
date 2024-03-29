<?php

declare(strict_types=1);

namespace Semperton\Database\ResultSet;

use PDO;
use PDOStatement;
use Semperton\Database\ResultSetInterface;

use function iterator_to_array;

final class PDOResultSet implements ResultSetInterface
{
	protected PDOStatement $statement;

	protected bool $executed;

	/** @var null|array<string, mixed> */
	protected ?array $current = null;

	protected int $position = -1;

	public function __construct(PDOStatement $statement)
	{
		$this->statement = $statement;

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
		$this->execute();

		$count = 0;
		while (false !== $this->statement->fetch(PDO::FETCH_LAZY)) {
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

		/** @var false|array<string, mixed> */
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
		$this->statement->execute();
		$this->executed = true;
	}
}
