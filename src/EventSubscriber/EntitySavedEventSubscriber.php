<?php

namespace App\EventSubscriber;

use App\Entity\Movie;
use App\Event\EntitySavedEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsDoctrineListener(event: Events::postPersist)]
class EntitySavedEventSubscriber
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        // Vérifiez si l'entité est de type Movie
        if ($entity instanceof Movie) {
            $event = new EntitySavedEvent($entity);
            // Déclenchez l'événement personnalisé
            $this->eventDispatcher->dispatch($event);
        }
    }
}
