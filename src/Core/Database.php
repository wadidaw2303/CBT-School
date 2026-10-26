<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private string $tablePrefix = '';
    private static ?self $instance = null;
    private array $queryLog = [];
    private bool $logging = false;

    private function __construct(array $config)
    {
        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $this->tablePrefix = $config['prefix'] ?? '';

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options'] ?? []);
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            if (empty($config)) {
                $config = config('database.connections.' . config('database.default'));
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $this->tablePrefix . $table);
    }

    public function select(string $query, array $bindings = []): array
    {
        $stmt = $this->execute($query, $bindings);
        return $stmt->fetchAll();
    }

    public function selectOne(string $query, array $bindings = []): array|false
    {
        $stmt = $this->execute($query, $bindings);
        return $stmt->fetch() ?: false;
    }

    public function insert(string $table, array $data): int|false
    {
        $table = $this->tablePrefix . $table;
        $columns = array_keys($data);
        $placeholders = ':' . implode(', :', $columns);
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

        $this->execute($sql, $data);
        return (int) $this->pdo->lastInsertId();
    }

    public function insertGetId(string $table, array $data): int
    {
        return $this->insert($table, $data);
    }

    public function update(string $table, array $data, string $where, array $bindings = []): int
    {
        $table = $this->tablePrefix . $table;
        $set = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        $stmt = $this->execute($sql, array_merge($data, $bindings));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $bindings = []): int
    {
        $table = $this->tablePrefix . $table;
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->execute($sql, $bindings);
        return $stmt->rowCount();
    }

    public function statement(string $sql): bool
    {
        return $this->pdo->exec($sql) !== false;
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function execute(string $sql, array $bindings = []): \PDOStatement
    {
        $this->logQuery($sql, $bindings);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    public function raw(string $sql, array $bindings = []): array
    {
        return $this->select($sql, $bindings);
    }

    public function migrate(string $path = null): int
    {
        $path ??= config('database.migrations.path', 'database/migrations');
        $path = base_path($path);

        if (!is_dir($path)) {
            throw new \RuntimeException("Migrations directory not found: {$path}");
        }

        $this->ensureMigrationsTable();

        $ran = $this->getRanMigrations();
        $files = glob($path . '/*.sql');
        sort($files);

        $count = 0;
        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $ran)) {
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql === false) {
                continue;
            }

            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if ($statement) {
                    $this->statement($statement);
                }
            }

            $this->recordMigration($filename);
            $count++;
        }

        return $count;
    }

    public function rollback(int $steps = 1): int
    {
        $this->ensureMigrationsTable();
        $ran = array_reverse($this->getRanMigrations());

        $count = 0;
        foreach ($ran as $filename) {
            if ($count >= $steps) break;

            $downFile = str_replace('.sql', '_down.sql', $filename);
            $downPath = config('database.migrations.path', 'database/migrations') . '/' . $downFile;

            if (file_exists(base_path($downPath))) {
                $sql = file_get_contents(base_path($downPath));
                if ($sql !== false) {
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($statements as $statement) {
                        if ($statement) {
                            $this->statement($statement);
                        }
                    }
                }
            }

            $this->deleteMigrationRecord($filename);
            $count++;
        }

        return $count;
    }

    private function ensureMigrationsTable(): void
    {
        $table = $this->tablePrefix . config('database.migrations.table', 'migrations');
        $this->statement("
            CREATE TABLE IF NOT EXISTS {$table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function getRanMigrations(): array
    {
        $table = $this->tablePrefix . config('database.migrations.table', 'migrations');
        return array_column($this->select("SELECT migration FROM {$table} ORDER BY batch, id"), 'migration');
    }

    private function recordMigration(string $filename): void
    {
        $table = $this->tablePrefix . config('database.migrations.table', 'migrations');
        $batch = (int) $this->selectOne("SELECT COALESCE(MAX(batch), 0) + 1 as batch FROM {$table}")['batch'] ?? 1;
        $this->insert($table, ['migration' => $filename, 'batch' => $batch]);
    }

    private function deleteMigrationRecord(string $filename): void
    {
        $table = $this->tablePrefix . config('database.migrations.table', 'migrations');
        $this->delete($table, 'migration = :migration', ['migration' => $filename]);
    }

    public function enableQueryLog(): void
    {
        $this->logging = true;
    }

    public function disableQueryLog(): void
    {
        $this->logging = false;
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    private function logQuery(string $sql, array $bindings): void
    {
        if ($this->logging) {
            $this->queryLog[] = [
                'sql' => $sql,
                'bindings' => $bindings,
                'time' => microtime(true),
            ];
        }
    }

    public function lastInsertId(string $sequence = null): string
    {
        return $this->pdo->lastInsertId($sequence);
    }
}

class QueryBuilder
{
    private Database $db;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private array $columns = ['*'];
    private array $joins = [];
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $groups = [];
    private array $havings = [];

    public function __construct(Database $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function select(array|string $columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where(string $column, string $operator, mixed $value, string $boolean = 'and'): self
    {
        $this->wheres[] = compact('column', 'operator', 'value', 'boolean');
        if (!in_array(strtoupper($operator), ['BETWEEN', 'NOT BETWEEN', 'IN', 'NOT IN'], true)) {
            $this->bindings[] = $value;
        } elseif (is_array($value)) {
            $this->bindings = array_merge($this->bindings, $value);
        } else {
            $this->bindings[] = $value;
        }
        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'and'): self
    {
        return $this->where($column, 'IN', $values, $boolean);
    }

    public function whereNotIn(string $column, array $values, string $boolean = 'and'): self
    {
        return $this->where($column, 'NOT IN', $values, $boolean);
    }

    public function whereNull(string $column, string $boolean = 'and'): self
    {
        return $this->where($column, 'IS', null, $boolean);
    }

    public function whereNotNull(string $column, string $boolean = 'and'): self
    {
        return $this->where($column, 'IS NOT', null, $boolean);
    }

    public function whereBetween(string $column, array $values, string $boolean = 'and'): self
    {
        return $this->where($column, 'BETWEEN', $values, $boolean);
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        return $this->where($column, $operator, $value, 'or');
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): self
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function groupBy(string ...$columns): self
    {
        $this->groups = array_merge($this->groups, $columns);
        return $this;
    }

    public function having(string $column, string $operator, mixed $value, string $boolean = 'and'): self
    {
        $this->havings[] = compact('column', 'operator', 'value', 'boolean');
        $this->bindings[] = $value;
        return $this;
    }

    public function limit(int $value): self
    {
        $this->limit = $value;
        return $this;
    }

    public function offset(int $value): self
    {
        $this->offset = $value;
        return $this;
    }

    public function forPage(int $page, int $perPage = 15): self
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    public function get(): array
    {
        return $this->buildSelect()->fetchAll();
    }

    public function first(): array|false
    {
        $this->limit = 1;
        return $this->buildSelect()->fetch() ?: false;
    }

    public function find(mixed $id, array $columns = ['*']): array|false
    {
        return $this->select($columns)->where('id', '=', $id)->first();
    }

    public function findOrFail(mixed $id, array $columns = ['*']): array
    {
        $result = $this->find($id, $columns);
        if (!$result) {
            abort(404, "Record not found in {$this->table}");
        }
        return $result;
    }

    public function count(string $column = '*'): int
    {
        $originalColumns = $this->columns;
        $this->columns = ["COUNT({$column}) as aggregate"];
        $result = $this->first();
        $this->columns = $originalColumns;
        return (int) ($result['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function sum(string $column): float
    {
        $originalColumns = $this->columns;
        $this->columns = ["SUM({$column}) as aggregate"];
        $result = $this->first();
        $this->columns = $originalColumns;
        return (float) ($result['aggregate'] ?? 0);
    }

    public function avg(string $column): float
    {
        $originalColumns = $this->columns;
        $this->columns = ["AVG({$column}) as aggregate"];
        $result = $this->first();
        $this->columns = $originalColumns;
        return (float) ($result['aggregate'] ?? 0);
    }

    public function min(string $column): mixed
    {
        $originalColumns = $this->columns;
        $this->columns = ["MIN({$column}) as aggregate"];
        $result = $this->first();
        $this->columns = $originalColumns;
        return $result['aggregate'] ?? null;
    }

    public function max(string $column): mixed
    {
        $originalColumns = $this->columns;
        $this->columns = ["MAX({$column}) as aggregate"];
        $result = $this->first();
        $this->columns = $originalColumns;
        return $result['aggregate'] ?? null;
    }

    public function insert(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function insertGetId(array $data): int
    {
        return $this->db->insertGetId($this->table, $data);
    }

    public function insertOrIgnore(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = ':' . implode(', :', $columns);
        $sql = "INSERT IGNORE INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        $this->db->execute($sql, $data);
        return (int) $this->db->getPdo()->lastInsertId();
    }

    public function update(array $data): int
    {
        $whereSql = $this->buildWhere();
        $set = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$set} {$whereSql}";
        return $this->db->execute($sql, array_merge($data, $this->bindings))->rowCount();
    }

    public function increment(string $column, int $amount = 1, array $extra = []): int
    {
        $data = array_merge([$column => $this->raw("{$column} + {$amount}")], $extra);
        return $this->update($data);
    }

    public function decrement(string $column, int $amount = 1, array $extra = []): int
    {
        $data = array_merge([$column => $this->raw("{$column} - {$amount}")], $extra);
        return $this->update($data);
    }

    public function delete(): int
    {
        $whereSql = $this->buildWhere();
        $sql = "DELETE FROM {$this->table} {$whereSql}";
        return $this->db->execute($sql, $this->bindings)->rowCount();
    }

    public function truncate(): bool
    {
        return $this->db->statement("TRUNCATE TABLE {$this->table}");
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $total = $this->count();
        $lastPage = (int) ceil($total / $perPage);
        $page = max(1, min($page, $lastPage));

        $this->forPage($page, $perPage);
        $data = $this->get();

        return [
            'data' => $data,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total ? ($page - 1) * $perPage + 1 : 0,
            'to' => min($page * $perPage, $total),
        ];
    }

    public function simplePaginate(int $perPage = 15, int $page = 1): array
    {
        $this->forPage($page, $perPage + 1);
        $data = $this->get();
        $hasMore = count($data) > $perPage;
        if ($hasMore) array_pop($data);

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'has_more' => $hasMore,
        ];
    }

    private function buildSelect(): \PDOStatement
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if ($this->joins) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }

        $whereSql = $this->buildWhere();
        if ($whereSql) {
            $sql .= " {$whereSql}";
        }

        if ($this->groups) {
            $sql .= " GROUP BY " . implode(', ', $this->groups);
            if ($this->havings) {
                $havingSql = $this->buildHaving();
                if ($havingSql) {
                    $sql .= " {$havingSql}";
                }
            }
        }

        if ($this->orders) {
            $orderSql = implode(', ', array_map(
                fn($o) => "{$o['column']} {$o['direction']}",
                $this->orders
            ));
            $sql .= " ORDER BY {$orderSql}";
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $this->db->execute($sql, $this->bindings);
    }

    private function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $clauses = [];
        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? 'WHERE' : strtoupper($where['boolean']);
            $column = $where['column'];
            $operator = $where['operator'];
            $value = $where['value'];

            if ($value === null && in_array(strtoupper($operator), ['IS', 'IS NOT'], true)) {
                $clauses[] = "{$boolean} {$column} {$operator} NULL";
            } elseif (in_array(strtoupper($operator), ['IN', 'NOT IN'], true) && is_array($value)) {
                $placeholders = implode(', ', array_fill(0, count($value), '?'));
                $clauses[] = "{$boolean} {$column} {$operator} ({$placeholders})";
            } elseif (strtoupper($operator) === 'BETWEEN' && is_array($value) && count($value) === 2) {
                $clauses[] = "{$boolean} {$column} BETWEEN ? AND ?";
            } else {
                $clauses[] = "{$boolean} {$column} {$operator} ?";
            }
        }

        return implode(' ', $clauses);
    }

    private function buildHaving(): string
    {
        if (empty($this->havings)) {
            return '';
        }

        $clauses = [];
        foreach ($this->havings as $i => $having) {
            $boolean = $i === 0 ? 'HAVING' : strtoupper($having['boolean']);
            $clauses[] = "{$boolean} {$having['column']} {$having['operator']} ?";
        }

        return implode(' ', $clauses);
    }

    public function raw(string $value): string
    {
        return $value;
    }

    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if ($this->joins) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }

        $whereSql = $this->buildWhere();
        if ($whereSql) {
            $sql .= " {$whereSql}";
        }

        if ($this->groups) {
            $sql .= " GROUP BY " . implode(', ', $this->groups);
        }

        if ($this->orders) {
            $orderSql = implode(', ', array_map(
                fn($o) => "{$o['column']} {$o['direction']}",
                $this->orders
            ));
            $sql .= " ORDER BY {$orderSql}";
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }
}