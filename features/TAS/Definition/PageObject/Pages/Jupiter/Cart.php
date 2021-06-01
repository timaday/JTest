<?php


namespace TAS\Definition\PageObject\Pages\Jupiter;


use TAS\Definition\PageObject\PageObjectSupport;

class Cart extends PageObjectSupport
{
    protected $pageName = 'cart';

    public function __construct($driver = false, $args = [])
    {
        parent::__construct($driver, $args);
    }


}