<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Images;
use App\Entity\Tricks;
use App\Entity\User;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use App\Service\ImagesService;
use App\Service\ToolsService;
use App\Service\VideosService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TricksController extends AbstractController
{
    /**
     * @Route("/", name="tricks_index", methods={"GET"})
     */
    public function index(
        TricksRepository $tricksRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {

        // todo : my own paginator coz no bundle
        // paginator test
        $data = $tricksRepository->getAllActivesTricks();
//        $data = $tricksRepository->findAll();
        $tricks = $paginator->paginate(
            $data, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            8
        );

        // we set a custom template for the pagination_render
        $tricks->setTemplate('component/_pagination.html.twig');

        return $this->render(
            'tricks/index.html.twig',
            [
                'tricks' => $tricks,
            ]
        );
    }

    /**
     * @Route("/tricks/new", name="tricks_new", methods={"GET","POST"})
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
     * @Route("/tricks/{id}-{slug}", name="tricks_show", methods={"GET", "POST"})
     */
    public function show(
        Request $request,
        EntityManagerInterface $entityManager,
        Tricks $trick
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

        return $this->render(
            'tricks/show.html.twig',
            [
                'trick' => $trick,
                'form' => $form->createView(),
            ]
        );
    }

    /**
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
     * @Route("/tricks/{id}", name="tricks_delete", methods={"DELETE"})
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


//    TODO : test if i can edit with url, no button (csrfToken ??)
// TODO : recup user courant et verif si il est owner du trick
    /**
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
