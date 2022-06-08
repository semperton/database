<?php

declare(strict_types=1);

namespace Semperton\Database;

interface ConnectionInterface
{
	public function execute(string $sql, ?array $params = null): bool;

	/**
	 * @return null|array<string, mixed>
	 */
	public function fetchRow(string $sql, ?array $params = null): ?array;

	/**
	 * @return iterable<int, array<string, mixed>>
	 */
	public function fetchAll(string $sql, ?array $params = null): iterable;

	public function fetchResult(string $sql, ?array $params = null): ResultSetInterface;

	/**
	 * @return false|scalar
	 */
	public function fetchValue(string $sql, ?array $params = null);

	public function inTransaction(): bool;

	public function beginTransaction(): bool;

	public function commit(): bool;

	public function rollBack(): bool;

	public function lastInsertId(): int;

	public function affectedRows(): int;
}
