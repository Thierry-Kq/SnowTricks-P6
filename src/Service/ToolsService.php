<?php


namespace App\Service;


use App\Entity\Tricks;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use function Symfony\Component\String\u;

class ToolsService
{
    public function slugify(Tricks $tricks, $params = null)
    {
        $slugger = new AsciiSlugger();

        if ($params) { // use UnicodeString to remove the 'q=' and then slug the string
            return $slugger->slug(u($params)->after('q=')->lower());
        }

        return $slugger->slug(u($tricks->getTitle())->lower());
    }

    public function videoRegex($url)
    {
        if (preg_match('/(?:https|http)?(?::\/\/)?(?:www.)?(youtube)(.com\/watch\?v=)([A-Za-z0-9-_]+)/', $url, $matches)) {
            $videoTitle = $matches[3];
            $videoType = 'youtube';
        } elseif (preg_match('/(?:https|http)?(?::\/\/)?(?:www.)?(dailymotion)(.com\/video\/)([A-Za-z0-9]+)/', $url, $matches)) {
            $videoTitle = $matches[3];
            $videoType = 'dailymotion';
        } else {
            return null;
        }

        return [
            'videoTitle' => $videoTitle,
            'videoType' => $videoType,
        ];
    }

//    to migrate to a video service
    public function addVideoTick(
        EntityManagerInterface $entityManager,
        Tricks $trick,
        $videos
    ) {
        foreach ($videos as $video) {
            $videoEntity = new Video();
            $urlParams = $this->videoRegex($video);

            if ($urlParams === null) {
                // todo : what to do if wrong link?
                // todo : cette partie a migrer dans un videoservice ainsi que le regex dans tools
                // todo : before save video in db, check if video exist (see discord discussion 07/11)
//                return $this->redirectToRoute('tricks_show', ['slug' => $trick->getSlug()]);
            } else {
                $videoTitle = $urlParams['videoTitle'];
                $videoType = $urlParams['videoType'];

                $videoEntity->setType($videoType);
                $videoEntity->setTitle($videoTitle);
                $videoEntity->setName($videoTitle);
                $videoEntity->addTrick($trick);

                $entityManager->persist($videoEntity);
                $entityManager->flush();
            }
        }
    }
}