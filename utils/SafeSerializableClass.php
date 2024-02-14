<?php

namespace utils;

/**
 * 安全序列化变量
 */
class SafeSerializableClass implements \Serializable
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    public function getData()
    {
        return $this->data;
    }
};
