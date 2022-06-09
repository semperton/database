<?php

declare(strict_types=1);

namespace Semperton\Database;

use Generator;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

use const SQLITE3_ASSOC;

final class SQLiteConnection implements ConnectionInterface
{
	/** @var string */
	protected $filename;

	/** @var bool */
	protected $enableExceptions;

	/** @var null|SQLite3 */
	protected $db;

	public function __construct(string $filename, bool $enableExceptions = true)
	{
		$this->filename = $filename;
		$this->enableExceptions = $enableExceptions;
	}

	public function getSQLite(): SQLite3
	{
		if ($this->db === null) {
			$this->db = new SQLite3($this->filename);
			$this->db->enableExceptions($this->enableExceptions);
		}

		return $this->db;
	}

	protected function prepare(string $sql, ?array $params): ?SQLite3Stmt
	{
		if ($stm = $this->getSQLite()->prepare($sql)) {

			if ($params) {

				/** @var mixed $value */
				foreach ($params as $param => $value) {

					if (is_int($param)) {
						$param++;
					}

					// in SQLite the type gets automatically
					// detected from the type of the value
					$stm->bindValue($param, $value);
				}
			}

			return $stm;
		}

		return null;
	}

	protected function exec(string $sql, ?array $params): ?SQLite3Result
	{
		if ($stm = $this->prepare($sql, $params)) {

			if ($result = $stm->execute()) {
				return $result;
			}
		}

		return null;
	}

	public function execute(string $sql, ?array $params = null): bool
	{
		if ($result = $this->exec($sql, $params)) {

			$result->finalize();

			return true;
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

	/**
	 * @psalm-suppress MixedReturnTypeCoercion
	 */
	public function fetchAll(string $sql, ?array $params = null): Generator
	{
		if ($result = $this->exec($sql, $params)) {

			while (false !== $record = $result->fetchArray(SQLITE3_ASSOC)) {
				yield $record;
			}

			$result->finalize();
		}
	}

	public function fetchResult(string $sql, ?array $params = null): ResultSetInterface
	{
		if ($result = $this->exec($sql, $params)) {

			return new SQLiteResultSet($result);
		}

		return new EmptyResultSet();
	}

	public function fetchValue(string $sql, ?array $params = null)
	{
		if ($result = $this->exec($sql, $params)) {

			$value = null;

			if ($result->numColumns() > 0) {

				/** @var false|scalar */
				$value = $result->fetchArray(SQLITE3_NUM)[0];
			}

			$result->finalize();

			return $value;
		}

		return false;
	}

	public function inTransaction(): bool
	{
		// TODO: how to check this
		return false;
	}

	public function beginTransaction(): bool
	{
		return $this->getSQLite()->exec('begin');
	}

	public function commit(): bool
	{
		return $this->getSQLite()->exec('commit');
	}

	public function rollBack(): bool
	{
		return $this->getSQLite()->exec('rollback');
	}

	public function lastInsertId(): int
	{
		return $this->getSQLite()->lastInsertRowID();
	}

	public function affectedRows(): int
	{
		return $this->getSQLite()->changes();
	}
}
