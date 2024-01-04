<?php declare(strict_types=1);

namespace App;

final class Database
{
    private $db;

    public function __construct(private readonly string $driver, ...$params)
    {
        $this->db = new $driver(...$params);
    }

    public function query($sql)
    {
        $query = $this->db->query($sql);

        return new class($query, $this->driver) {
            private $query;
            private string $driver;

            public function __construct($query, $driver)
            {
                $this->driver = $driver;
                $this->query = $query;
            }

            public function fetchArray()
            {
                return $this->fetch();
            }

            public function fetch_assoc()
            {
                return $this->fetch();
            }

            private function fetch()
            {
                if ($this->driver == 'SQLite3') {
                    return $this->query->fetchArray();
                }
                if ($this->driver == 'mysqli') {
                    return $this->query->fetch_assoc();
                }
                return false;
            }
        };
    }
}
