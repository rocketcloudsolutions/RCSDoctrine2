<?php
namespace RCSBase\Doctrine2;

class Listener implements \Doctrine\Common\EventSubscriber
{  
    private $driver;
    private $cachedMap;
    private $map;
  
    const ENTRY_ANNOTATION = 'RCSBase\Doctrine2\Discriminator\Entry';
  
    public function getSubscribedEvents() 
    {
        return Array( \Doctrine\ORM\Events::loadClassMetadata );  
    }  
  
    public function __construct( \Doctrine\ORM\Configuration $Configuration ) 
    {  
        $this->driver = $Configuration->getMetadataDriverImpl();  
        $this->cachedMap = Array();  
    }  
    
    private function extractEntry( $class ) 
    {  
        $annotations = \RCSBase\Doctrine2\Annotation\Annotations::getAnnotationsForClass( $class );
        $success = false;  
        
        foreach($annotations as $key => $annotation)
        {
            if( get_class($annotation) == self::ENTRY_ANNOTATION )
            {
                $value = $annotations[$key]->getValue();  

                if( in_array( $value, $this->map ) ) 
                {  
                    throw new Exception( "Found duplicate discriminator map entry '" . $value . "' in " . $class );  
                }  

                $this->map[$class] = $value;  
                $success =  true;  
            }  
        }

        return $success;  
    } 
    
    private function checkFamily( $class ) 
    {  
        $rc             = new \ReflectionClass( $class );  
        $is_base_class  = false;
        $annotations    = \RCSBase\Doctrine2\Annotation\Annotations::getAnnotationsForClass( $class );
        
        
        foreach($annotations as $annotation)
        {
            if(get_class($annotation) == "Doctrine\ORM\Mapping\InheritanceType")
                $is_base_class = true;
        }
        
        if( !$is_base_class && $rc->getParentClass() !== false ) 
        {  
            $parent = $rc->getParentClass()->name;
            $this->checkFamily( $parent );  
        } 
        else 
        {
            $this->cachedMap[$class]['isParent'] = true;
            $this->checkChildren( $class );  
        }  
    }  

    private function checkChildren( $class ) 
    {  
        foreach( $this->driver->getAllClassNames() as $name ) 
        {  
            $cRc            = new \ReflectionClass( $name );  
            $parent_class   = $cRc->getParentClass();
            $cParent        = $parent_class ? $parent_class->name : null;  

            if( ! array_key_exists( $name, $this->map )  
                && $cParent == $class && $this->extractEntry( $name ) ) 
            {
                $this->checkChildren( $name );  
            }  
        }  
    }
    
    private function overrideMetadata( \Doctrine\ORM\Event\LoadClassMetadataEventArgs $event, $class ) 
    {
        $event->getClassMetadata()->discriminatorMap =  
                $this->cachedMap[$class]['map'];  
        $event->getClassMetadata()->discriminatorValue =  
                $this->cachedMap[$class]['discr'];  

        if( isset( $this->cachedMap[$class]['isParent'] )  
            && $this->cachedMap[$class]['isParent'] === true ) 
        {
            $subclasses = $this->cachedMap[$class]['map'];  
            unset( $subclasses[$this->cachedMap[$class]['discr']] );  
            $event->getClassMetadata()->subClasses =  
                    array_values( $subclasses );  
        }  
    }  
  
    public function loadClassMetadata( \Doctrine\ORM\Event\LoadClassMetadataEventArgs $event ) 
    {
        $this->map  = Array();  
        $class      = $event->getClassMetadata()->name;  

        if( array_key_exists( $class, $this->cachedMap ) ) 
        {  
            $this->overrideMetadata( $event, $class );  
            return;  
        }  

        if(count($event->getClassMetadata()->discriminatorMap ) == 1
            && $this->extractEntry( $class ) ) {

            $this->checkFamily( $class );  
        } else return;

        $dMap = array_flip( $this->map );  
        foreach( $this->map as $cName => $discr ) {  
            $this->cachedMap[$cName]['map']     = $dMap;  
            $this->cachedMap[$cName]['discr']   = $this->map[$cName];  
        }  

        $this->overrideMetadata( $event, $class );  
    }  
} 