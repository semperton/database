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
	public function fetchAll(string $sql, array $params = []): ?ResultSetInterface;
	/**
	 * @return mixed
	 */
	public function fetchValue(string $sql, array $params = []);
	public function lastInsertId(): int;
	public function affectedRows(): int;
}
