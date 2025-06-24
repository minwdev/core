
<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gibbon\Database;

use PDO;
use PDOStatement;
use Gibbon\Contracts\Database\Connection as ConnectionInterface;
use Gibbon\Contracts\Database\Result as ResultInterface;
use Gibbon\Database\Result;

/**
 * SQLite Database Connection
 *
 * @version v26
 * @since   v26
 */
class SqliteConnection implements ConnectionInterface
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * Constructor
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get the underlying PDO connection
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a prepared statement with parameters
     *
     * @param string $statement
     * @param array $params
     * @return ResultInterface
     */
    public function select(string $statement, array $params = []): ResultInterface
    {
        $stmt = $this->pdo->prepare($statement);
        $stmt->execute($params);
        return new Result($stmt);
    }

    /**
     * Execute a prepared statement and return the first row
     *
     * @param string $statement
     * @param array $params
     * @return array|false
     */
    public function selectOne(string $statement, array $params = [])
    {
        $result = $this->select($statement, $params);
        return $result->fetch();
    }

    /**
     * Execute an insert statement
     *
     * @param string $statement
     * @param array $params
     * @return ResultInterface
     */
    public function insert(string $statement, array $params = []): ResultInterface
    {
        return $this->select($statement, $params);
    }

    /**
     * Execute an update statement
     *
     * @param string $statement
     * @param array $params
     * @return ResultInterface
     */
    public function update(string $statement, array $params = []): ResultInterface
    {
        return $this->select($statement, $params);
    }

    /**
     * Execute a delete statement
     *
     * @param string $statement
     * @param array $params
     * @return ResultInterface
     */
    public function delete(string $statement, array $params = []): ResultInterface
    {
        return $this->select($statement, $params);
    }

    /**
     * Prepare a statement
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function prepare(string $statement): PDOStatement
    {
        return $this->pdo->prepare($statement);
    }

    /**
     * Get the last insert ID
     *
     * @return string
     */
    public function lastInsertID(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Get the row count from the last statement
     *
     * @return int
     */
    public function rowCount(): int
    {
        return $this->pdo->query('SELECT changes()')->fetchColumn();
    }

    /**
     * Begin a transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Check if we're in a transaction
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Quote a string for use in a query
     *
     * @param string $string
     * @param int $parameterType
     * @return string
     */
    public function quote(string $string, int $parameterType = PDO::PARAM_STR): string
    {
        return $this->pdo->quote($string, $parameterType);
    }

    /**
     * Get connection statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        return [];
    }
}
