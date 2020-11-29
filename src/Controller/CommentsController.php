<?php


namespace App\Controller;


use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\ToolsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CommentsController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and comment.getUser() === user")
     * @Route("/comment/{id}/delete", name="trick_comment_delete", methods={"DELETE"})
     */
    public function deleteComment(
        Comment $comment,
        Request $request,
        EntityManagerInterface $entityManager
    ) {

        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $data['_token'])) {
            $entityManager->remove($comment);
            $entityManager->flush();

            // on repond en json
            return new JsonResponse(['success' => 1]);
        }

        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }

//    todo : le button annuler a l air de valider

    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and comment.getUser() === user")
     * @Route("/comment/{id}/edit", name="trick_comment_edit")
     */
    public function editComment(
        Request $request,
        Comment $comment,
        EntityManagerInterface $entityManager

    ) {

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($this->getUser() && $form->isSubmitted() && $form->isValid()) {

            $trick = $comment->getTrick();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('tricks_show', ['id' => $trick->getId(), 'slug' => $trick->getSlug()]);
        }

        return $this->render(
            'tricks/edit_comment.html.twig',
            [
                'trick' => $comment->getTrick()->getId(),
                'form' => $form->createView(),
            ]
        );
    }
}