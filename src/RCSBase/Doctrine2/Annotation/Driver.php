<?php
namespace RCSBase\Doctrine2\Annotation;

class Driver extends \Doctrine\ORM\Mapping\Driver\AnnotationDriver
{
    public function __construct($reader, $paths = null) 
    {
        parent::__construct($reader, $paths);
    }
}