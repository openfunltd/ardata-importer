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

    public static function find($id) {
        $db = DB::getInstance()->pdo;
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primary_key . " = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new static($row) : null;
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

    public function toOriginalArray() {
        $result = [];
        foreach ($this->attributes as $key => $value) {
            $result[$key] = $this->cast($key, $value);
        }
        return $result;
    }

    protected function cast($key, $value)
    {
        return $value;
    }
}
