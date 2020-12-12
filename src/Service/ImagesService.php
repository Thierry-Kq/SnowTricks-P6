<?php


namespace App\Service;


use App\Entity\Images;
use Doctrine\ORM\EntityManagerInterface;

class ImagesService
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function addImages($image, $entity, $path)
    {
//        dd(getimagesize($image));
        $fichier = md5(uniqid()) . '.' . $image->guessExtension();

        $image->move(
            $path,
            $fichier
        );
        $img = new Images();
        $img->setName($fichier);


        if ($this->entityManager->getMetadataFactory()->getMetadataFor(get_class($entity))->getName() === 'App\Entity\User') {
            $entity->setImage($img); // user
        } else if ($this->entityManager->getMetadataFactory()->getMetadataFor(get_class($entity))->getName() === 'App\Entity\Tricks') {
            $entity->addImage($img); // trick
            $this->entityManager->persist($entity);
        }
    }

    /**
     * delete img from bdd & from repository
     *
     * @param Images $image
     * @param        $path
     */
    public function deleteImage(
        Images $image,
        $path
    ) {
        $nom = $image->getName();
        if ($nom != 'placeholder.png') { // dont remove img placeholder from fixtures
            unlink($path); // supprime le fichier dans uploads
        }
//        unlink($path);
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }
}