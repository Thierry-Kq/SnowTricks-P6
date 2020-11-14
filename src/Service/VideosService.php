<?php


namespace App\Service;


use App\Entity\Tricks;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;

class VideosService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

    public function addVideoTick(
        Tricks $trick,
        $video
    ) {
        // * check if url exist (what to do if not ? error message ?)
        $headers = get_headers($video . '&format=json');
        if ($headers[0] === 'HTTP/1.0 200 OK') {
            $videoEntity = new Video();
            $urlParams = $this->videoRegex($video);

            if ($urlParams === null) {
                // todo : what to do if wrong link?
            } else {
                $videoTitle = $urlParams['videoTitle'];
                $videoType = $urlParams['videoType'];

                $videoEntity->setType($videoType);
                $videoEntity->setTitle($videoTitle);
                $videoEntity->setName($videoTitle);
                $videoEntity->addTrick($trick);

                $this->entityManager->persist($videoEntity);
                $this->entityManager->flush();
            }
        }
    }
}