<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ScratchController extends AbstractController
{
    /**
     * @Route("/scratch", name="scratch")
     */
    public function index()
    {
        return $this->render('scratch/index.html.twig', [
            'controller_name' => 'ScratchController',
        ]);
    }
}
