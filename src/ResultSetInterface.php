<?php

declare(strict_types=1);

namespace Semperton\Database;

use Countable;
use Iterator;

interface ResultSetInterface extends Iterator, Countable
{
	/**
	 * @return null|array<string, mixed>
	 */
	public function first(): ?array;
	public function toArray(): array;
}
