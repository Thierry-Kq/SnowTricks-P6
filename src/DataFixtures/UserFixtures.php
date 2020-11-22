<?php

namespace App\DataFixtures;

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

        $videoCollection = $this->createYoutubeVideos();

        $user = new User();
        $user->setUsername('SnowTricks')
            ->setEmail('azerty@azerty.com')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'azerty'))
            ->setRoles(['ROLE_ADMIN']);
        $this->manager->persist($user);

        $user = new User();
        $user->setUsername('Kasskq')
            ->setEmail('kasskq@gmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'azerty'))
            ->setRoles(['ROLE_ADMIN']);
        $this->manager->persist($user);

        $user = new User();
        $user->setUsername('Kasska')
            ->setEmail('azerty@gmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'azerty'));
        $this->manager->persist($user);


        for ($trickNumber = 1; $trickNumber <= 10; $trickNumber++) {
            $trick = new Tricks();
            $trick->setTitle('Mon trick numéro ' . $trickNumber . ' !')
                ->setDescription('Ma description de ce trick numéro ' . $trickNumber . ' !')
                ->setAuthor($user);
            foreach ($videoCollection as $video) {
                $trick->addVideo($video);
            }
            $this->manager->persist($trick);
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
