<?php

declare(strict_types=1);

namespace Semperton\Database;

use ArrayAccess;
use PDO;

final class Connection implements ConnectionInterface
{
	/** @var array */
	protected $options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
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

	protected function getPDO(): PDO
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
		$stm = $this->getPDO()->prepare($sql);

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

		return $results ? $results->first() : null;
	}

	public function fetchAll(string $sql, array $params = []): ?ResultSetInterface
	{
		$stm = $this->getPDO()->prepare($sql);

		if ($stm) {

			return new ResultSet($stm, $params);
		}

		return null;
	}

	/**
	 * @return false|scalar
	 */
	public function fetchValue(string $sql, array $params = [])
	{
		$stm = $this->getPDO()->prepare($sql);

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

	public function lastInsertId(?string $name = null): int
	{
		return (int)$this->getPDO()->lastInsertId($name);
	}

	public function affectedRows(): int
	{
		return $this->changes;
	}
}
