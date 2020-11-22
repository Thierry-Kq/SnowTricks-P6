<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Images;
use App\Entity\Trick;
use App\Entity\Tricks;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    private $passwordEncoder;
    /**
     * @var ObjectManager
     */
    private $manager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $manager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
    }

    public function load(ObjectManager $manager)
    {
//        $users = [];
        $videoCollection = $this->createYoutubeVideos();

        $userFirst = new User();
        $userFirst->setUsername('SnowTricks')
            ->setEmail('azerty@azerty.com')
            ->setPassword($this->passwordEncoder->encodePassword($userFirst, 'azerty'))
            ->setRoles(['ROLE_ADMIN']);
        $this->manager->persist($userFirst);
//        $users[] = [$userFirst];

        $userSecond = new User();
        $userSecond->setUsername('Kasskq')
            ->setEmail('kasskq@gmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($userSecond, 'azerty'))
            ->setRoles(['ROLE_ADMIN']);
        $this->manager->persist($userSecond);
//        $users[] = [$userSecond];

        $userThird = new User();
        $userThird->setUsername('Kasska')
            ->setEmail('azerty@gmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($userThird, 'azerty'));
        $this->manager->persist($userThird);
//        $users[] = [$userThird];


        for ($trickNumber = 1; $trickNumber <= 10; $trickNumber++) {
            $trick = new Tricks();
            $trick->setTitle('Mon trick numéro ' . $trickNumber . ' !')
                ->setDescription('Ma description de ce trick numéro ' . $trickNumber . ' !')
                ->setAuthor($userThird);
            foreach ($videoCollection as $video) {
                $trick->addVideo($video);
            }
            for ($commentNumber = 1; $commentNumber <= 10; $commentNumber++) {

                $comment = new Comment();
                $comment->setContent('Voici mon commentaire n° ' . $commentNumber . ' pour le trick n° ' . $trickNumber);

                $randomUser = ($commentNumber % 2 === 0) ? $userSecond : $userThird;
                $comment->setUser($randomUser);
                $comment->setTrick($trick);

                $this->manager->persist($trick);
                $this->manager->persist($comment);
                $this->manager->flush();
            }

            $this->addPlaceholerImage($trick);
        }
    }

    public function createYoutubeVideos()
    {
        $youtubeVideos = [
            '8AWdZKMTG3U',
            'gbHU6J6PRRw',
            'SQyTWk7OxSI',
            'monyw0mnLZg',
        ];

        $videos = [];
        foreach ($youtubeVideos as $youtubeVideo) {
            $video = new Video();
            $video->setTitle($youtubeVideo);
            $video->setName($youtubeVideo);
            $video->setType('youtube');
            $videos[] = $video;
            $this->manager->persist($video);
            $this->manager->flush();
        }

        return $videos;
    }

    public function addPlaceholerImage($entity)
    {
        $image = new Images();
        $image->setName('placeholder.png');
        $image->setTricks($entity);
        $this->manager->persist($image);
        $this->manager->flush();
    }
}
