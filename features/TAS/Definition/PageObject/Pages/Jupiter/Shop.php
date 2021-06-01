<?php


namespace TAS\Definition\PageObject\Pages\Jupiter;


use TAS\Definition\PageObject\PageObjectSupport;

class Shop extends PageObjectSupport
{
    protected $pageName = 'shop';

    public function __construct($driver = false, $args = [])
    {
        parent::__construct($driver, $args);
    }

    public function addProductToBasket($productName)
    {
        $this->clickElement('BuyProduct', ["productName" => $productName], 1, true);
    }
}