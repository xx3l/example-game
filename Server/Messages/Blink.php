<?php 

namespace Server\Messages;

class Blink
{
    public $entity = null;
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_BLINK, 
                $this->entity->id,
        );
    }
}

