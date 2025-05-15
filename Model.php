<?php

abstract class Model
{
    protected $attributes = [];

    protected static $table = '';
    protected static $primary_key = 'id';
    protected static $schema = [];

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

    protected function cast($key, $value)
    {
        return $value;
    }
}
