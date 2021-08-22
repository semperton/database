<div align="center">
<a href="https://github.com/semperton">
<img src="https://avatars0.githubusercontent.com/u/76976189?s=140" alt="Semperton">
</a>
<h1>Semperton Database</h1>
<p>A compact PDO wrapper library.</p>
//
</div>
<hr>

## Installation

Just use Composer:

```
composer require semperton/database
```
Database requires PHP 7.2+

## Connection

The ```Connection``` class is a PDO wrapper.
The constructor accepts the same parameters as the PDO constructor.

```PHP
use Semperton\Database\Connection;

$connection = new Connection('dsn', null, null, [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$database = $connection->getDatabase(); // PDO object
$connection->setDatabase($database);

// ConnectionInterface
execute(string $sql, array $params = []): bool // execute a sql statement
fetchRow(string $sql, array $params = []): ?array // fetch a single row (assoc)
fetchAll(string $sql, array $params = []): ?ResultSetInterface // fetch all rows as a result set
fetchValue(string $sql, array $params = []) // fetch a single value
lastInsertId(): int // get the last insert id
affectedRows(): int // get affected rows
```

## ResultSet

The ```ResultSet``` class is a wrapper around ```PDOStatement::fetch``` calls.
It's an ```Iterator``` with additional ```first()```, ```count()```, and ```toArray()``` methods.

```PHP
$users = $connection->fetchAll('select * from user limit :limit', ['limit' => 5]);

$firstUser = $users->first();

foreach($users as $user){
	// ...
}

$userCount = $users->count();
$userArray = $users->toArray();
```