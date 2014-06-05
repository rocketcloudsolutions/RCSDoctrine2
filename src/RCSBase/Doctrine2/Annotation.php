<?php
namespace RCSBase\Doctrine2;

Annotation::$reader = new \Doctrine\Common\Annotations\AnnotationReader();  

require_once 'DiscriminatorEntry.php';

class Annotation 
{  
    public static $reader;  

    public static function getAnnotationsForClass( $className ) 
    {
        return Annotation::$reader->getClassAnnotations(new \ReflectionClass( $className ));
    }  
}  