<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\Tricks;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        $user = new User();
        $user->setUsername('SnowTricks')
            ->setEmail('azerty@azerty.com')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'azerty'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('Kasskq')
            ->setEmail('kasskq@gmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'azerty'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);


        $trick = new Tricks();
        $trick->setTitle('Mon premier trick !')
            ->setDescription('Ma description de ce premier trick !')
            ->setAuthor($user);
        $manager->persist($trick);

        $user = new User();

        $user->setUsername('Kasska')
            ->setEmail('azerty@gmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'azerty'));
        $manager->persist($user);

        $trick = new Tricks();
        $trick->setTitle('Mon second trick !')
            ->setDescription('Ma description de ce second trick !')
            ->setAuthor($user);
        $manager->persist($trick);

        $manager->flush();
    }
}
