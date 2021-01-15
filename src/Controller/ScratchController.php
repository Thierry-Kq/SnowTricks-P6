<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\User;
use App\Form\TrickType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ScratchController extends AbstractController
{
    /**
     * @Route("/conditions-d-utilisation", name="conditions")
     */
    public function condition()
    {
        return $this->render('scratch/conditions.html.twig');
    }

}
