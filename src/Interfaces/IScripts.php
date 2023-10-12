<?php
namespace Interfaces;
use AppConfig;
use Overwrite\CliMenuBuilder;

interface IScripts {
    static function init(AppConfig $conf, CliMenuBuilder $menu) : static;
    function install();
    function remove();
    function update();
    function checkVersion() : ?int;
}