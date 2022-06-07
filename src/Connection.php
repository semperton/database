<?php

declare(strict_types=1);

namespace Semperton\Database;

use ArrayAccess;
use Generator;
use PDO;

final class Connection implements ConnectionInterface
{
	/** @var array */
	protected $options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false
	];

	/** @var string */
	private $dsn;

	/** @var null|string */
	private $username;

	/** @var null|string */
	private $password;

	/** @var null|PDO */
	protected $pdo = null;

	/** @var int */
	protected $changes = 0;

	public function __construct(
		string $dsn,
		?string $username = null,
		?string $password = null,
		array $options = []
	) {
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->options = $options + $this->options;
	}

	public function getPdo(): PDO
	{
		if ($this->pdo === null) {
			$this->pdo = new PDO(
				$this->dsn,
				$this->username,
				$this->password,
				$this->options
			);
		}

		return $this->pdo;
	}

	public function execute(string $sql, array $params = []): bool
	{
		$stm = $this->getPdo()->prepare($sql);

		if ($stm) {

			$result = $stm->execute($params);
			$this->changes = $stm->rowCount();

			$stm->closeCursor();

			return $result;
		}

		return false;
	}

	public function fetchRow(string $sql, array $params = []): ?array
	{
		$results = $this->fetchAll($sql, $params);

		$first = $results->current();

		/** @var null|array<string, mixed> $first */
		return $first;
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @return Generator<int, array<string, mixed>>
	 */
	public function fetchAll(string $sql, array $params = []): Generator
	{
		$stm = $this->getPdo()->prepare($sql);

		if ($stm) {

			$stm->execute($params);

			while (false !== $record = $stm->fetch(PDO::FETCH_ASSOC)) {
				yield $record;
			}

			$stm->closeCursor();
		}
	}

	public function fetchResult(string $sql, array $params = []): ResultSetInterface
	{
		$stm = $this->getPdo()->prepare($sql);

		if ($stm) {

			return new ResultSet($stm, $params);
		}

		return new EmptyResultSet();
	}

	public function fetchValue(string $sql, array $params = [])
	{
		$stm = $this->getPdo()->prepare($sql);

		if ($stm) {

			$stm->execute($params);

			/** @var false|ArrayAccess */
			$result = $stm->fetch(PDO::FETCH_LAZY);
			/** @var scalar */
			$value = $result ? $result[0] : false;

			$stm->closeCursor();

			return $value;
		}

		return false;
	}

	public function inTransaction(): bool
	{
		return $this->getPdo()->inTransaction();
	}

	public function beginTransaction(): bool
	{
		return $this->getPdo()->beginTransaction();
	}

	public function commit(): bool
	{
		return $this->getPdo()->commit();
	}

	public function rollBack(): bool
	{
		return $this->getPdo()->rollBack();
	}

	public function lastInsertId(?string $name = null): int
	{
		return (int)$this->getPdo()->lastInsertId($name);
	}

	public function affectedRows(): int
	{
		return $this->changes;
	}
}
