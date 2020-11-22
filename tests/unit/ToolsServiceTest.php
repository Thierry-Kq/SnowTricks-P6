<?php


namespace App\Tests\unit;


use App\Entity\Tricks;
use App\Service\ToolsService;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Test\TestCase;

class ToolsServiceTest extends TestCase
{

    public function testSlugifyWithParams()
    {

        $tools = new ToolsService();
        $trick = new Tricks();
        $trick->setTitle('Mon premier Trick !');

        $stringToSlugify = "q=Trick de la mort qui tue ! @éé";
        $this->assertEquals($tools->slugify($trick, $stringToSlugify), 'trick-de-la-mort-qui-tue-ee');
    }

    public function testSlugifyWithoutParams()
    {
        $tools = new ToolsService();
        $trick = new Tricks();
        $trick->setTitle('Mon premier Trick');

        $this->assertEquals($tools->slugify($trick), 'mon-premier-trick');
    }
}