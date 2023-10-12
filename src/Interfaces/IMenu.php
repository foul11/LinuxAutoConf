<?php
namespace Interfaces;

interface IMenu {
    function execute();
    function setTitle(string $title);
}