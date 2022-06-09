<?php

declare(strict_types=1);

namespace Semperton\Database;

use Generator;

interface ConnectionInterface
{
	/**
	 * @param null|array<int, mixed>|array<string, mixed> $params
	 */
	public function execute(string $sql, ?array $params = null): bool;

	/**
	 * @param null|array<int, mixed>|array<string, mixed> $params
	 * @return null|array<string, mixed>
	 */
	public function fetchRow(string $sql, ?array $params = null): ?array;

	/**
	 * @param null|array<int, mixed>|array<string, mixed> $params
	 * @return Generator<int, array<string, mixed>>
	 */
	public function fetchAll(string $sql, ?array $params = null): Generator;

	/**
	 * @param null|array<int, mixed>|array<string, mixed> $params
	 */
	public function fetchResult(string $sql, ?array $params = null): ResultSetInterface;

	/**
	 * @param null|array<int, mixed>|array<string, mixed> $params
	 * @return false|null|scalar
	 */
	public function fetchValue(string $sql, ?array $params = null);

	public function inTransaction(): bool;

	public function beginTransaction(): bool;

	public function commit(): bool;

	public function rollBack(): bool;

	public function lastInsertId(): int;

	public function affectedRows(): int;
}
