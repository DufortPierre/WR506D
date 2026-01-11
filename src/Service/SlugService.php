<?php

namespace App\Service;

use Cocur\Slugify\Slugify;

class SlugService
{
    private Slugify $slugify;

    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    /**
     * Convertit une phrase en version slugifiée
     * 
     * @param string $text Le texte à slugifier
     * @return string Le texte slugifié
     */
    public function slugify(string $text): string
    {
        return $this->slugify->slugify($text);
    }
}
