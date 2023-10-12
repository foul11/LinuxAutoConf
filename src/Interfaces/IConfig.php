<?php
namespace Interfaces;

interface IConfig {
    function get(string $name);
    function set(string $name, $value);
    function unset(string $name);
    function isset(string $name);
    function diff();
    function changes();
    function __toString();
}