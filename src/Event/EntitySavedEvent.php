<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Movie;

class EntitySavedEvent extends Event
{
    private Movie $entity;

    public function __construct(Movie $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): Movie
    {
        return $this->entity;
    }
}
