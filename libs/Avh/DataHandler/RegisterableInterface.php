<?php
namespace Avh\DataHandler;

interface RegistrableInterface
{
    public function set($key, $value);

    public function get($key);

    public function clear();
}