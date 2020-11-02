<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\ImagesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route ("/dashboard", name="app_dashboard")
     */
    public function dashboard(
        Request $request,
        EntityManagerInterface $entityManager,
        ImagesService $imagesService,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $passwordForm = $this->createForm(ChangePasswordType::class, $user);
        $passwordForm->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('images')->getData();
            if ($image !== null) {
                $oldImage = $user->getImage() ?: null;
                $imagesService->addImages($image, $user, $this->getParameter('images_directory'));
            }
            $entityManager->persist($user);
            $entityManager->flush();
            if ($image !== null && $oldImage) {
                $imagesService->deleteImage($oldImage, $this->getParameter('images_directory') . '/' . $oldImage->getName());
            }
        }
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $passwordForm->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render(
            'security/dashboard.html.twig',
            [
                'form' => $form->createView(),
                'passwordForm' => $passwordForm->createView(),
            ]
        );
    }

    /**
     * @Route("/delete-account/{id}", name="app_delete_user", methods={"DELETE"})
     */
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        User $user,
        UserRepository $userRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $snowTricksAccount = $userRepository->findOneBy(['id' => 1]);
            foreach ($user->getTricks() as $trick) {
                $trick->setAuthor($snowTricksAccount);
                $entityManager->persist($trick);
                $entityManager->flush();
            }

            $entityManager->remove($user);
            $entityManager->flush();

            $session = new Session();
            $session->invalidate();

            return $this->redirectToRoute('app_logout');
        }

        return $this->redirectToRoute('homepage');
    }
}
