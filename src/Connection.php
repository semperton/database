<?php

declare(strict_types=1);

namespace Semperton\Database;

use PDO;
use PDOStatement;

final class Connection implements ConnectionInterface
{
	/** @var array */
	protected $options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false
	];

	/** @var string */
	protected $dsn;

	/** @var null|string */
	protected $username;

	/** @var null|string */
	protected $password;

	/** @var null|PDO */
	protected $instance;

	/** @var int */
	protected $changes = 0;

	public function __construct(
		string $dsn,
		?string $username = null,
		?string $password = null,
		?array $options = null
	) {
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		if ($options) {
			$this->options = $options + $this->options;
		}
	}

	public function getDatabase(): PDO
	{
		if ($this->instance === null) {
			$this->instance = new PDO(
				$this->dsn,
				$this->username,
				$this->password,
				$this->options
			);
		}

		return $this->instance;
	}

	public function execute(string $sql, array $params = []): bool
	{
		$stm = $this->prepare($sql);
		$result = $stm->execute($params);

		$this->changes = $stm->rowCount();

		return $result;
	}

	protected function prepare(string $sql): PDOStatement
	{
		$database = $this->getDatabase();
		$stm = $database->prepare($sql);

		return $stm;
	}

	public function fetchRow(string $sql, array $params = []): ?array
	{
		$results = $this->fetchAll($sql, $params);

		return $results->first();
	}

	public function fetchAll(string $sql, array $params = []): ResultSetInterface
	{
		$stm = $this->prepare($sql);
		$stm->execute($params);

		return new ResultSet($stm);
	}

	/**
	 * @return mixed
	 */
	public function fetchValue(string $sql, array $params = [])
	{
		$stm = $this->prepare($sql);
		$stm->execute($params);

		/** @var false|\ArrayAccess */
		$result = $stm->fetch(PDO::FETCH_LAZY);

		return $result === false ? null : $result[0];
	}

	public function lastInsertId(): int
	{
		$database = $this->getDatabase();

		return (int)$database->lastInsertId();
	}

	public function affectedRows(): int
	{
		return $this->changes;
	}
}
