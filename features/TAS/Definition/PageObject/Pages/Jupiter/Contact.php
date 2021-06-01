<?php


namespace TAS\Definition\PageObject\Pages\Jupiter;


use TAS\Definition\PageObject\PageObjectSupport;

class Contact extends PageObjectSupport
{
    protected $pageName = 'contact';

    public function __construct($driver = false, $args = [])
    {
        parent::__construct($driver, $args);
    }

    public function getErrorMessageOfField($logicalName)
    {
        return $this->getText($logicalName . "Error", [], 5, false);
    }
}