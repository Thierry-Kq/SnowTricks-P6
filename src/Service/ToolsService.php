<?php


namespace App\Service;


use App\Entity\Tricks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ToolsService
{
    public function slugify(Tricks $tricks)
    {
        $slugger = new AsciiSlugger();

        return $slugger->slug($tricks->getTitle());
    }
}