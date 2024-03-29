<?php

declare(strict_types=1);

namespace Semperton\Database\Connection;

use ArrayAccess;
use Generator;
use PDO;
use PDOStatement;
use Semperton\Database\ConnectionInterface;
use Semperton\Database\ResultSet\EmptyResultSet;
use Semperton\Database\ResultSet\PDOResultSet;
use Semperton\Database\ResultSetInterface;

use function key;
use function array_unshift;
use function is_int;
use function is_bool;
use function is_null;

final class PDOConnection implements ConnectionInterface
{
	protected array $options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false
	];

	private string $dsn;

	private ?string $username;

	private ?string $password;

	protected ?PDO $pdo = null;

	protected int $changes = 0;

	/** @var null|callable */
	protected $initCallback;

	public function __construct(
		string $dsn,
		?string $username = null,
		?string $password = null,
		array $options = [],
		?callable $initCallback = null
	) {
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->initCallback = $initCallback;
		$this->options = $options + $this->options;
	}

	public function getPDO(): PDO
	{
		if ($this->pdo === null) {

			$this->pdo = new PDO(
				$this->dsn,
				$this->username,
				$this->password,
				$this->options
			);

			if ($this->initCallback) {
				($this->initCallback)($this->pdo);
			}
		}

		return $this->pdo;
	}

	protected function prepare(string $sql, ?array $params): ?PDOStatement
	{
		if ($stm = $this->getPDO()->prepare($sql)) {

			if ($params) {

				if (key($params) === 0) {
					array_unshift($params, null);
					unset($params[0]);
				}

				/** @var mixed $value */
				foreach ($params as $param => $value) {

					$type = PDO::PARAM_STR;

					if (is_int($value)) {
						$type = PDO::PARAM_INT;
					} else if (is_bool($value)) {
						$type = PDO::PARAM_BOOL;
					} else if (is_null($value)) {
						$type = PDO::PARAM_NULL;
					}

					$stm->bindValue($param, $value, $type);
				}
			}

			return $stm;
		}

		return null;
	}

	public function execute(string $sql, ?array $params = null): bool
	{
		if ($stm = $this->prepare($sql, $params)) {

			$result = $stm->execute();
			$this->changes = $stm->rowCount();

			$stm->closeCursor();

			return $result;
		}

		return false;
	}

	public function fetchRow(string $sql, ?array $params = null): ?array
	{
		$results = $this->fetchAll($sql, $params);

		$first = $results->current();

		/** @var null|array<string, mixed> $first */
		return $first;
	}

	public function fetchColumn(string $sql, ?array $params = null, int $column = 0): Generator
	{
		if ($stm = $this->prepare($sql, $params)) {

			$stm->execute();

			/** @psalm-suppress InvalidArrayAccess */
			while (false !== $record = $stm->fetch(PDO::FETCH_LAZY)) {
				yield $record[$column];
			}

			$stm->closeCursor();
		}
	}

	public function fetchAll(string $sql, ?array $params = null): Generator
	{
		if ($stm = $this->prepare($sql, $params)) {

			$stm->execute();

			while (false !== $record = $stm->fetch(PDO::FETCH_ASSOC)) {
				yield $record;
			}

			$stm->closeCursor();
		}
	}

	public function fetchResult(string $sql, ?array $params = null): ResultSetInterface
	{
		if ($stm = $this->prepare($sql, $params)) {

			return new PDOResultSet($stm);
		}

		return new EmptyResultSet();
	}

	public function fetchValue(string $sql, ?array $params = null)
	{
		if ($stm = $this->prepare($sql, $params)) {

			$stm->execute();

			/** @var false|ArrayAccess */
			$result = $stm->fetch(PDO::FETCH_LAZY);
			/** @var false|null|scalar */
			$value = $result ? $result[0] : false;

			$stm->closeCursor();

			return $value;
		}

		return false;
	}

	public function inTransaction(): bool
	{
		return $this->getPDO()->inTransaction();
	}

	public function beginTransaction(): bool
	{
		return $this->getPDO()->beginTransaction();
	}

	public function commit(): bool
	{
		return $this->getPDO()->commit();
	}

	public function rollBack(): bool
	{
		return $this->getPDO()->rollBack();
	}

	public function lastInsertId(): int
	{
		return (int)$this->getPDO()->lastInsertId();
	}

	public function affectedRows(): int
	{
		return $this->changes;
	}
}
