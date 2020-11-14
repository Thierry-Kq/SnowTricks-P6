<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Tricks;
use App\Entity\User;
use App\Entity\Video;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use App\Service\ImagesService;
use App\Service\ToolsService;
use App\Service\VideosService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        EntityManagerInterface $entityManager,
        ImagesService $imagesService,
        VideosService $videosService
    ) {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');

        $trick = new Tricks();
        $form = $this->createForm(TricksType::class, $trick);
        $trick->setAuthor($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // get images here
            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                $imagesService->addImages($image, $trick, $this->getParameter('images_directory'));
            }

            $videos = $form->get('videos')->getData();
            foreach (array_filter($videos) as $video) {
                $videosService->addVideoTick($trick, array_filter($video));
            }
            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->redirectToRoute('tricks_index');
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
     * @Route("/{id}-{slug}", name="tricks_show", methods={"GET"})
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
    public function edit(
        Request $request,
        Tricks $trick,
        EntityManagerInterface $entityManager,
        ImagesService $imagesService,
        VideosService $videosService,
        ToolsService $tools
    ): Response {

        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

//        $user = $this->getUser();
        // if not logged = bug
        // deny access if not author or admin
//        if ($user->getId() != $trick->getAuthor()->getId()) {
//            $this->denyAccessUnlessGranted('ROLE_ADMIN');
//        }

        if ($form->isSubmitted() && $form->isValid()) {
            // get images here
            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                $imagesService->addImages($image, $trick, $this->getParameter('images_directory'));
            }

            $videos = $form->get('videos')->getData();
            foreach (array_filter($videos) as $video) {
                $videosService->addVideoTick($trick, $video);
            }
            $entityManager->flush();

//            return $this->redirectToRoute('trick', ['id' => $trick->getId()]);
            return $this->redirectToRoute(
                'tricks_show',
                [
                    'id' => $trick->getId(),
                    'slug' => $trick->getSlug(),
                ]
            );
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

            foreach ($trick->getImages() as $img) {
                $nom = $img->getName();
                unlink($this->getParameter('images_directory') . '/' . $nom); // supprime le fichier dans uploads
                $trick->removeImage($img); // supprime le trickId dans image
                // les images seront delete en base car orphanRemoval=true dans la relation Tricks->Images
            }
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tricks_index');
    }

    /**
     * @Route("/delete/image/{id}", name="tricks_image_delete", methods={"DELETE"})
     */
    public function deleteImage(
        Images $image,
        Request $request,
        ImagesService $imagesService
    ) {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            $imagesService->deleteImage($image, $this->getParameter('images_directory') . '/' . $image->getName());

            // on repond en json
            return new JsonResponse(['success' => 1]);
        }

        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }

    /**
     * @Route("/delete/video/{id}", name="tricks_video_delete", methods={"DELETE"})
     */
    public function deleteVideo(
        Video $video,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $video->getId(), $data['_token'])) {
            $entityManager->remove($video);
            $entityManager->flush();

            // on repond en json
            return new JsonResponse(['success' => 1]);
        }

        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }


//    TODO : test if i can edit with url, no button (csrfToken ??)

    /**
     * @Route("/{id}/edit-slug", name="edit_slug")
     */
    public function editSlug(
        Request $request,
        Tricks $trick,
        EntityManagerInterface $entityManager,
        ToolsService $tools
    ) {

        $slug = ($request->getContent() === "") ? $tools->slugify($trick) : $tools->slugify($trick, $request->getContent());
        $trick->setSlug($slug);
        $entityManager->persist($trick);
        $entityManager->flush();

        return $this->redirectToRoute(
            'tricks_show',
            [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug(),
            ]
        );
    }
}
