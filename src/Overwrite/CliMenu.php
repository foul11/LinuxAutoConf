<?php
namespace Overwrite;

class CliMenu extends \PhpSchool\CliMenu\CliMenu {
    public function setTitle(string $title) : void {
        parent::setTitle(\CliMenuReflects::genCentredTitle($this, $title));
    }
}