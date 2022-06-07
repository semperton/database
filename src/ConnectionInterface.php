<?php

declare(strict_types=1);

namespace Semperton\Database;

interface ConnectionInterface
{
	public function execute(string $sql, array $params = []): bool;

	/**
	 * @return null|array<string, mixed>
	 */
	public function fetchRow(string $sql, array $params = []): ?array;

	/**
	 * @return iterable<int, array<string, mixed>>
	 */
	public function fetchAll(string $sql, array $params = []): iterable;

	public function fetchResult(string $sql, array $params = []): ResultSetInterface;

	/**
	 * @return false|scalar
	 */
	public function fetchValue(string $sql, array $params = []);

	public function inTransaction(): bool;

	public function beginTransaction(): bool;

	public function commit(): bool;

	public function rollBack(): bool;

	public function lastInsertId(?string $name = null): int;

	public function affectedRows(): int;
}
