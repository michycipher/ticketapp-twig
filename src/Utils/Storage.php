<?php
// src/Utils/Storage.php
class Storage {
    private $base;

    public function __construct($base = __DIR__ . '/../data') {
        $this->base = realpath($base);
    }

    private function file($name) {
        $path = $this->base . '/' . $name;
        if (!file_exists($path)) {
            file_put_contents($path, json_encode([]));
        }
        return $path;
    }

    public function read($name) {
        $data = @file_get_contents($this->file($name));
        return json_decode($data, true) ?: [];
    }

    public function write($name, $array) {
        return file_put_contents($this->file($name), json_encode(array_values($array), JSON_PRETTY_PRINT));
    }
}
