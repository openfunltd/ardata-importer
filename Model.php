<?php

abstract class Model
{
    protected $attributes = [];

    protected static $table = '';
    protected static $primary_key = 'id';
    protected static $schema = [];
    protected static $elastic_mappings = [];

    public function __construct($attributes = [])
    {
        foreach (static::$schema as $field) {
            $this->attributes[$field] = $attributes[$field] ?? null;
        }
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        if (in_array($key, static::$schema)) {
            $this->attributes[$key] = $value;
        }
    }

    public static function getPrimaryKey()
    {
        return static::$primary_key;
    }

    public function getPrimaryKeyValue() {
        $pk = static::$primary_key;
        return $this->attributes[$pk] ?? null;
    }

    public function toElasticData()
    {
        $data = $this->attributes;

        //unset columns with empty value
        $columns = array_keys($data);
        foreach ($columns as $col) {
            if (empty($data[$col]) and $data[$col] !== false) {
                unset($data[$col]);
            }
        }
        //unset versioning column
        unset($data['updatedDate']);

        //prepend domain into download url
        foreach (['downloadPdf', 'downloadCsv', 'downloadZip'] as $key) {
            if (!empty($data[$key])) {
                $data[$key] = 'https://ardata.cy.gov.tw' . $data[$key];
            }
        }

        return (object) $data;
    }

    public static function find($id) {
        $db = DB::getInstance()->pdo;
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primary_key . " = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $instance = new static($row);
        foreach ($instance->attributes as $key => $value) {
            $instance->attributes[$key] = $instance->uncast($key, $value);
        }

        return $instance;
    }

    public static function where($conditions = [])
    {
        $db = DB::getInstance()->pdo;
        $where_parts = [];
        $params = [];

        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if (!in_array($key, static::$schema)) {
                    continue;
                }
                $where_parts[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }

            if (empty($where_parts)) {
                throw new Exception("No valid conditions provided for where().");
            }

            $sql = "SELECT * FROM " . static::$table . " WHERE " . implode(' AND ', $where_parts);
        } else {
            $sql = "SELECT * FROM " . static::$table;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $instance = new static($row);
            foreach ($instance->attributes as $key => $value) {
                $instance->attributes[$key] = $instance->uncast($key, $value);
            }
            $results[] = $instance;
        }

        return $results;
    }

    public function save()
    {
        $db = DB::getInstance()->pdo;
        $data = $this->attributes;

        foreach ($data as $key => $value) {
            $data[$key] = $this->cast($key, $value);
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO " . static::$table .
            "(" . implode(', ', $columns) . ")" .
            " VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($data)
    {
        $db = DB::getInstance()->pdo;

        $pk = static::$primary_key;
        $pk_value = $this->attributes[$pk] ?? null;

        if ($pk_value === null) {
            throw new Exception("Cannot update without primary key value.");
        }

        $updates = [];
        foreach ($data as $key => $value) {
            if ($key !== $pk && in_array($key, static::$schema)) {
                $updates[$key] = $this->cast($key, $value);
            }
        }

        if (empty($updates)) {
            return false;
        }

        $set = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($updates)));
        $sql = "UPDATE " . static::$table . " SET {$set} WHERE {$pk} = :_pk";

        $stmt = $db->prepare($sql);
        $updates['_pk'] = $pk_value;
        $success = $stmt->execute($updates);

        if ($success) {
            foreach ($updates as $key => $value) {
                if ($key !== '_pk') {
                    $this->attributes[$key] = $value;
                }
            }
        }

        return $success;
    }

    protected function cast($key, $value)
    {
        return $value;
    }

    protected function uncast($key, $value)
    {
        return $value;
    }
}
