<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/conditions-d-utilisation", name="conditions")
     */
    public function condition()
    {
        return $this->render('default/conditions.html.twig');
    }

}
