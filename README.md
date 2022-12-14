<div align="center">
<a href="https://github.com/semperton">
<img width="140" src="https://raw.githubusercontent.com/semperton/.github/main/readme-logo.svg" alt="Semperton">
</a>
<h1>Semperton Database</h1>
<p>A compact PDO / SQLite wrapper library.</p>
</div>

---

## Installation

Just use Composer:

```
composer require semperton/database
```
Database requires PHP 7.4+

## Connection

The ```PDOConnection``` class is a ```PDO``` wrapper.
Its constructor accepts the same parameters as the ```PDO``` constructor.

```PHP
use Semperton\Database\Connection\PDOConnection;

$connection = new PDOConnection('dsn', null, null, [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
```

The internal PDO object is lazily created, when you make the first database call.
You can pass a ```callable``` as the 5th argument to the ```PDOConnection``` constructor
that gets called, when PDO first initializes.

## Interface

```PHP
interface ConnectionInterface
{
	public function execute(string $sql, ?array $params = null): bool;
	public function fetchRow(string $sql, ?array $params = null): ?array;
	public function fetchColumn(string $sql, ?array $params = null, int $column = 0): Generator;
	public function fetchAll(string $sql, ?array $params = null): Generator;
	public function fetchResult(string $sql, ?array $params = null): ResultSetInterface;
	public function fetchValue(string $sql, ?array $params = null);
	public function inTransaction(): bool;
	public function beginTransaction(): bool;
	public function commit(): bool;
	public function rollBack(): bool;
	public function lastInsertId(): int;
	public function affectedRows(): int;
}
```

## ResultSet

The ```ResultSet``` class is a wrapper around ```PDOStatement::execute``` and ```PDOStatement::fetch``` calls.
It's an ```Iterator``` with additional ```first()```, ```count()``` and ```toArray()``` methods.

```PHP
$users = $connection->fetchAll('select * from user limit :limit', ['limit' => 5]);

$firstUser = $users->first();

foreach($users as $user){
	// ...
}

$userCount = $users->count();
$userArray = $users->toArray();
```
