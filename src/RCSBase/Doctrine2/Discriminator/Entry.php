<?php
namespace RCSBase\Doctrine2\Discriminator;

class Entry
{  
    private $value;

    public function __construct( array $data ) 
    {  
        $this->value = $data['value'];  
    }  

    public function getValue() 
    {  
        return $this->value;  
    }  
}  