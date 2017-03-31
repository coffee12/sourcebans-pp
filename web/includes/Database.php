<?php

namespace SourceBans;

class Database
{
    private $instance = null;
    private $prefix = null;
    private $pdo = null;
    private $stmt = null;

    protected function __clone()
    {
    }

    protected function __construct($user, $password, $host, $port, $dbname, $prefix)
    {
        $this->prefix = $prefix;

        $dsn = 'mysql:host='.$host.';port='.$port.';dbname='.$dbname;
        $options = array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );

        try {
            $this->pdo = new \PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

    private function replacePrefix($query)
    {
        return str_replace(':prefix', $this->prefix, $query);
    }

    public function query($query)
    {
        $query = $this->replacePrefix($query);
        $this->stmt = $this->pdo->prepare($query);
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function bindMultiple($data = array())
    {
        foreach ($data as $param => $value) {
            $this->bind($param, $value);
        }
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function resultset()
    {
        $this->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    public function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Database(DB_USER, urlencode(DB_PASS), DB_HOST, DB_PORT, DB_NAME, DB_PREFIX);
        }
        return self::$instance;
    }

    protected function __destruct()
    {
        unset($this->pdo);
    }
}
