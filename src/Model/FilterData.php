<?php

namespace App\Model;

use App\Entity\Categories;

class FilterData
{
    /** @var int */
    public $page = 1;
    
    /** @var string */ 
    public $titre= '';

    /** @var string */
    public $auteur= '';

    /** @var Categories[] */
    public $categories = [];

    /** @var null|float */
    public $prixMin;

    /** @var null|float */
    public $prixMax;
}