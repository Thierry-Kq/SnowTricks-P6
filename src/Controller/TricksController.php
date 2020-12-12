<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Images;
use App\Entity\Tricks;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TricksType;
use App\Repository\CommentRepository;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class TricksController extends AbstractController
{
    /**
     * @Route("/", name="tricks_index", methods={"GET"})
     */
    public function index(
        TricksRepository $tricksRepository,
        Request $request
    ): Response {

        $limit = 8;
        $page = $request->query->getInt('page', 1);
        $page = $page === 0 ? 1 : $page;
        $tricks = $tricksRepository->getPaginatedTricks($page, $limit);
        $total = $tricksRepository->getTotalTricks();


        return $this->render(
            'tricks/index.html.twig',
            [
                'tricks' => $tricks,
                'total' => $total,
                'limit' => $limit,
                'page' => $page,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/tricks/new", name="tricks_new", methods={"GET","POST"})
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ImagesService $imagesService,
        VideosService $videosService
    ) {
        $trick = new Tricks();
        $form = $this->createForm(TricksType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $trick->setAuthor($user);

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
     * @Route("/tricks/{id}-{slug}", name="tricks_show", methods={"GET", "POST"})
     */
    public function show(
        Request $request,
        EntityManagerInterface $entityManager,
        Tricks $trick,
        CommentRepository $commentRepository
    ): Response {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($this->getUser() && $form->isSubmitted() && $form->isValid()) {

            $comment->setTrick($trick);
            $comment->setUser($this->getUser());

            $entityManager->persist($comment);
            $entityManager->flush();
        }

        $limit = 6;
        $page = $request->query->getInt('page', 1);
        $page = $page === 0 ? 1 : $page;
        $comments = $commentRepository->getPaginatedComments($page, $limit, $trick->getId());
        $total = $commentRepository->getTotalCommentsByOneTrick($trick->getId());

        return $this->render(
            'tricks/show.html.twig',
            [
                'trick' => $trick,
                'form' => $form->createView(),
                'limit' => $limit,
                'page' => $page,
                'comments' => $comments,
                'total' => $total,
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and trick.getAuthor() === user")
     * @Route("/tricks/{id}/edit", name="tricks_edit", methods={"GET","POST"})
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and trick.getAuthor() === user")
     * @Route("/tricks/{id}", name="tricks_delete", methods={"DELETE"})
     */
    public function delete(
        Request $request,
        Tricks $trick,
        EntityManagerInterface $entityManager
    ): Response {

        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $data['_token'])) {
            foreach ($trick->getImages() as $img) {
                $nom = $img->getName();
                if ($nom != 'placeholder.png') { // dont remove img placeholder from fixtures
                    unlink($this->getParameter('images_directory') . '/' . $nom); // supprime le fichier dans uploads
                }
                $trick->removeImage($img); // supprime le trickId dans image
                // les images seront delete en base car orphanRemoval=true dans la relation Tricks->Images
            }
            $entityManager->remove($trick);
            $entityManager->flush();

            // on repond en json
            return new JsonResponse(['success' => 1]);
        }

        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")
     * @Route("/tricks/delete/image/{id}", name="tricks_image_delete", methods={"DELETE"})
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and trick.getAuthor() === user")
     * @Route("/tricks/delete/video/{id}", name="tricks_video_delete", methods={"DELETE"})
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

    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and trick.getAuthor() === user")
     * @Route("/tricks/{id}/edit-slug", name="edit_slug")
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
