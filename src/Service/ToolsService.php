<?php


namespace App\Service;


use App\Entity\Tricks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use function Symfony\Component\String\u;

class ToolsService
{
    public function slugify(Tricks $tricks, $params = null)
    {
        $slugger = new AsciiSlugger();

        if ($params) { // use UnicodeString to remove the 'q=' and then slug the string
            return $slugger->slug(u($params)->after('q='));
        }

        return $slugger->slug($tricks->getTitle());
    }
}