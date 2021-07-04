<?php

declare(strict_types=1);

namespace Semperton\Database;

use Iterator;

interface ResultSetInterface extends Iterator
{
	/**
	 * @return null|array<string, mixed>
	 */
	public function first(): ?array;
	public function count(): int;
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array;
}
