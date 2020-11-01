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
     * @Route("/", name="homepage")
     */
    public function index()
    {


        return $this->render('homepage.html.twig');
    }

    /**
     * @Route("/scratch", name="scratch")
     */
    public function scratch(MailerInterface $mailer)
    {


        return $this->render('scratch/index.html.twig');
    }
}
