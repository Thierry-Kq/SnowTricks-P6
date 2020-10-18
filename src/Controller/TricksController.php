<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Entity\User;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tricks")
 */
class TricksController extends AbstractController
{
    /**
     * @Route("/", name="tricks_index", methods={"GET"})
     */
    public function index(TricksRepository $tricksRepository): Response
    {
        return $this->render(
            'tricks/index.html.twig',
            [
                'tricks' => $tricksRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="tricks_new", methods={"GET","POST"})
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');

        $trick = new Tricks();
        $form = $this->createForm(TricksType::class, $trick);
        $trick->setAuthor($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->redirectToRoute('tricks_index');
//            return $this->redirectToRoute('trick', ['id' => $trick->getId()]);

        }

        return $this->render(
            'tricks/new.html.twig',
            [
                'trick' => $trick,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="tricks_show", methods={"GET"})
     */
    public function show(Tricks $trick): Response
    {
        return $this->render(
            'tricks/show.html.twig',
            [
                'trick' => $trick,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="tricks_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Tricks $trick): Response
    {
        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);


        $user = $this->getUser();

        // deny access if not author or admin
        if ($user->getId() != $trick->getAuthor()->getId()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

//            return $this->redirectToRoute('trick', ['id' => $trick->getId()]);

            return $this->redirectToRoute('tricks_index');
        }

        return $this->render(
            'tricks/edit.html.twig',
            [
                'trick' => $trick,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="tricks_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Tricks $trick): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tricks_index');
    }
}
