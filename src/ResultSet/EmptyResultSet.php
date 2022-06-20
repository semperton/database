<?php

declare(strict_types=1);

namespace Semperton\Database\ResultSet;

use EmptyIterator;
use Semperton\Database\ResultSetInterface;

final class EmptyResultSet extends EmptyIterator implements ResultSetInterface
{
	public function count(): int
	{
		return 0;
	}

	public function first(): ?array
	{
		return null;
	}

	public function toArray(): array
	{
		return [];
	}
}
