<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CircularReferenceHandler
{
    public function __invoke($object, string $format = null, array $context = []): string|int
    {
        // Retourner l'ID de l'objet pour éviter les références circulaires
        if (method_exists($object, 'getId')) {
            return $object->getId();
        }
        
        // Sinon, retourner le nom de la classe
        return get_class($object);
    }
}
