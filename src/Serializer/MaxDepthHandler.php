<?php

namespace App\Serializer;

class MaxDepthHandler
{
    public function __invoke($object, string $format = null, array $context = []): array
    {
        // Retourner un tableau avec l'ID et le type pour les objets trop profonds
        if (method_exists($object, 'getId')) {
            return [
                'id' => $object->getId(),
                'type' => get_class($object),
            ];
        }
        
        return ['type' => get_class($object)];
    }
}
