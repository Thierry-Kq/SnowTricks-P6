<?php


namespace App\Service;


use App\Entity\Images;
use Doctrine\ORM\EntityManagerInterface;

class ImagesService
{

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function addImages($image, $entity, $path)
    {
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
     */
    public function deleteImage(
        Images $image,
        $path
    ) {
        unlink($path);
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }
}