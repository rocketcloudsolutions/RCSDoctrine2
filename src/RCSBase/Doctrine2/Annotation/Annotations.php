<?php
namespace RCSBase\Doctrine2\Annotation;

Annotations::$reader = new \Doctrine\Common\Annotations\AnnotationReader();

class Annotations
{  
    public static $reader;  

    public static function getAnnotationsForClass( $className ) 
    {
        return Annotations::$reader->getClassAnnotations(new \ReflectionClass( $className ));
    }  
}  