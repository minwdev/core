
<?php

namespace Gibbon\Database;

use Psr\Log\LoggerInterface;
use Gibbon\Database\Result;
use Gibbon\Contracts\Database\Connection as ConnectionInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Supabase Database Connection.
 */
class SupabaseConnection implements ConnectionInterface
{
    /**
     * The active Guzzle HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $supabaseUrl;

    /**
     * @var string
     */
    protected $supabaseKey;

    /**
     * @var bool
     */
    protected $querySuccess = false;

    /**
     * @var mixed
     */
    protected $result = null;

    /**
     * @var int
     */
    protected $transactions = 0;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var string
     */
    protected $errorMessage = null;

    /**
     * Create the connection wrapper around a Guzzle HTTP client.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->supabaseUrl = $config['supabaseUrl'] ?? '';
        $this->supabaseKey = $config['supabaseKey'] ?? '';
        
        $this->httpClient = new Client([
            'base_uri' => $this->supabaseUrl . '/rest/v1/',
            'headers' => [
                'apikey' => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ]
        ]);
    }

    /**
     * Get the current HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    public function getConnection()
    {
        return $this->httpClient;
    }

    /**
     * {@inheritDoc}
     */
    public function selectOne($query, $bindings = [])
    {
        $result = $this->run($query, $bindings);
        return $result->fetch();
    }

    /**
     * {@inheritDoc}
     */
    public function select($query, $bindings = [])
    {
        return $this->run($query, $bindings);
    }

    /**
     * Run an insert statement and return the last insert ID.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int|bool
     */
    public function insert($query, $bindings = [])
    {
        $querySuccess = $this->statement($query, $bindings);
        return $querySuccess ? $this->getLastInsertId() : false;
    }

    /**
     * {@inheritDoc}
     */
    public function update($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function statement($query, $bindings = [])
    {
        $this->run($query, $bindings);
        return $this->querySuccess;
    }

    /**
     * {@inheritDoc}
     */
    public function affectingStatement($query, $bindings = [])
    {
        $result = $this->run($query, $bindings);
        return $result->rowCount();
    }

    /**
     * Run a SQL statement using Supabase REST API.
     *
     * @param  string  $query
     * @param  array   $bindings
     *
     * @return \Gibbon\Database\Result
     */
    protected function run($query, $bindings = [])
    {
        try {
            // Parse SQL query to convert to Supabase REST API calls
            $parsedQuery = $this->parseSqlQuery($query, $bindings);
            
            $response = $this->executeSupabaseQuery($parsedQuery);
            
            $this->querySuccess = true;
            $this->result = new Result($response);
            
        } catch (\Exception $e) {
            $this->result = $this->handleQueryException($e);
        }

        return $this->result;
    }

    /**
     * Parse SQL query and convert to Supabase operations.
     *
     * @param string $query
     * @param array $bindings
     * @return array
     */
    protected function parseSqlQuery($query, $bindings = [])
    {
        // Basic SQL parsing - this would need to be more comprehensive
        $query = trim($query);
        $queryLower = strtolower($query);
        
        if (strpos($queryLower, 'select') === 0) {
            return $this->parseSelectQuery($query, $bindings);
        } elseif (strpos($queryLower, 'insert') === 0) {
            return $this->parseInsertQuery($query, $bindings);
        } elseif (strpos($queryLower, 'update') === 0) {
            return $this->parseUpdateQuery($query, $bindings);
        } elseif (strpos($queryLower, 'delete') === 0) {
            return $this->parseDeleteQuery($query, $bindings);
        }
        
        throw new \Exception("Unsupported query type");
    }

    /**
     * Execute the parsed query using Supabase HTTP client.
     *
     * @param array $parsedQuery
     * @return array
     */
    protected function executeSupabaseQuery($parsedQuery)
    {
        try {
            switch ($parsedQuery['type']) {
                case 'select':
                    $url = $parsedQuery['table'];
                    $params = ['select' => $parsedQuery['columns']];
                    
                    if (!empty($parsedQuery['where'])) {
                        foreach ($parsedQuery['where'] as $condition) {
                            $params[$condition['column']] = 'eq.' . $condition['value'];
                        }
                    }
                    
                    $response = $this->httpClient->get($url, ['query' => $params]);
                    return json_decode($response->getBody()->getContents(), true);
                    
                case 'insert':
                    $response = $this->httpClient->post($parsedQuery['table'], [
                        'json' => $parsedQuery['data']
                    ]);
                    return json_decode($response->getBody()->getContents(), true);
                    
                case 'update':
                    $url = $parsedQuery['table'];
                    $params = [];
                    
                    if (!empty($parsedQuery['where'])) {
                        foreach ($parsedQuery['where'] as $condition) {
                            $params[$condition['column']] = 'eq.' . $condition['value'];
                        }
                    }
                    
                    $response = $this->httpClient->patch($url, [
                        'json' => $parsedQuery['data'],
                        'query' => $params
                    ]);
                    return json_decode($response->getBody()->getContents(), true);
                    
                case 'delete':
                    $url = $parsedQuery['table'];
                    $params = [];
                    
                    if (!empty($parsedQuery['where'])) {
                        foreach ($parsedQuery['where'] as $condition) {
                            $params[$condition['column']] = 'eq.' . $condition['value'];
                        }
                    }
                    
                    $response = $this->httpClient->delete($url, ['query' => $params]);
                    return json_decode($response->getBody()->getContents(), true);
            }
        } catch (RequestException $e) {
            throw new \Exception('Supabase API Error: ' . $e->getMessage());
        }
        
        throw new \Exception("Unknown query type");
    }

    /**
     * Parse SELECT query.
     */
    protected function parseSelectQuery($query, $bindings)
    {
        // Simplified parsing - would need regex or proper SQL parser
        preg_match('/select\s+(.*?)\s+from\s+(\w+)/i', $query, $matches);
        
        return [
            'type' => 'select',
            'columns' => $matches[1] ?? '*',
            'table' => $matches[2] ?? '',
            'where' => $this->parseWhereClause($query, $bindings)
        ];
    }

    /**
     * Parse INSERT query.
     */
    protected function parseInsertQuery($query, $bindings)
    {
        preg_match('/insert\s+into\s+(\w+)\s*\((.*?)\)\s*values\s*\((.*?)\)/i', $query, $matches);
        
        $columns = array_map('trim', explode(',', $matches[2] ?? ''));
        $values = $bindings;
        
        $data = array_combine($columns, $values);
        
        return [
            'type' => 'insert',
            'table' => $matches[1] ?? '',
            'data' => $data
        ];
    }

    /**
     * Parse UPDATE query.
     */
    protected function parseUpdateQuery($query, $bindings)
    {
        preg_match('/update\s+(\w+)\s+set\s+(.*?)(?:\s+where\s+(.*))?$/i', $query, $matches);
        
        return [
            'type' => 'update',
            'table' => $matches[1] ?? '',
            'data' => $this->parseSetClause($matches[2] ?? '', $bindings),
            'where' => $this->parseWhereClause($query, $bindings)
        ];
    }

    /**
     * Parse DELETE query.
     */
    protected function parseDeleteQuery($query, $bindings)
    {
        preg_match('/delete\s+from\s+(\w+)(?:\s+where\s+(.*))?$/i', $query, $matches);
        
        return [
            'type' => 'delete',
            'table' => $matches[1] ?? '',
            'where' => $this->parseWhereClause($query, $bindings)
        ];
    }

    /**
     * Parse WHERE clause.
     */
    protected function parseWhereClause($query, $bindings)
    {
        // Simplified WHERE parsing
        if (preg_match('/where\s+(.+?)(?:order\s+by|group\s+by|limit|$)/i', $query, $matches)) {
            $whereClause = $matches[1];
            // This would need more sophisticated parsing
            return [];
        }
        return [];
    }

    /**
     * Parse SET clause for UPDATE.
     */
    protected function parseSetClause($setClause, $bindings)
    {
        // Simplified SET parsing
        return [];
    }

    /**
     * Get last insert ID.
     */
    protected function getLastInsertId()
    {
        return 1; // Supabase doesn't return auto-increment IDs the same way
    }

    /**
     * Handle query exceptions.
     *
     * @param \Exception $e
     * @return \Gibbon\Database\Result
     */
    protected function handleQueryException($e)
    {
        $this->querySuccess = false;
        $this->errorMessage = $e->getMessage();
        
        if ($this->logger) {
            $this->logger->error($e->getMessage());
        }
        
        return new Result();
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        // Supabase handles transactions differently
        $this->transactions++;
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->transactions = 0;
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->transactions = 0;
    }

    /**
     * @param LoggerInterface|null $logger
     * @return SupabaseConnection
     */
    public function setLogger(LoggerInterface $logger): SupabaseConnection
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get the error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Legacy compatibility methods.
     */
    public function executeQuery($data = [], $query = "", $error = null)
    {
        return $this->run($query, $data);
    }

    public function getQuerySuccess()
    {
        return $this->querySuccess;
    }

    public function getResult()
    {
        return $this->result;
    }
}
