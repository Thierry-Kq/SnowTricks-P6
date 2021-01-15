<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setActivationToken(md5(uniqid()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // TODO : constraint uniq name trick
            // TODO ATTENTION ! ERROR SI LE MAIL 'does not comply with addr-spec of RFC 2822.' par exemple eazezae (pas de @.com)
            $email = (new Email())
                ->from('noreply@snowtricks.com')
                ->to($user->getEmail())
                ->subject('Snowtricks - Validation de votre inscription')
//                ->text('Texte html?')
                ->html($this->renderView('emails/activation.html.twig', ['token' => $user->getActivationToken()]));

            $mailer->send($email);

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'registration/register.html.twig',
            [
                'registrationForm' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/activation/{token}", name="activation")
     */
    public function activation(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        $token
    ) {
        $user = $userRepository->findOneBy(['activationToken' => $token]);

        if (!$user) {
            $this->addFlash(
                'notice',
                'Not Found'
            );
//            throw new CustomUserMessageAuthenticationException('Username could not be found.');
            // TODO : 404 ?
        } else {
            $user->setActivationToken(null);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('homepage');
    }
}
