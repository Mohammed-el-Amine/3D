<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/inscription', name: 'app_inscription', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $created_at = new \DateTimeImmutable();
        $form = $this->createFormBuilder($user)
            ->add('username')
            ->add('email')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'checked' => true,
                    'style' => 'display:none;',
                ],
            ])
            ->add('created_at', HiddenType::class, [
                'data' => $created_at->format('Y-m-d H:i:s'),
            ])
            ->add('update_at', HiddenType::class, [
                'data' => $created_at->format('Y-m-d H:i:s'),
            ])
            ->add('register', SubmitType::class, ['label' => 'S\'inscrire'])
            ->getForm();

        $form->remove('active');
        $form->remove('created_at');
        $form->remove('update_at');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail(),]);
            $existingUsername = $entityManager->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);

            if ($existingUser) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée.', 'custom-error-class');
            } else if ($existingUsername) {
                $this->addFlash('error', 'Ce nom d\'utilisateur est déja utilisé.', 'custom-error-class');
            } else {

                $passwordClaire = $user->getPassword();
                $passwordHasher = hash('sha256', $passwordClaire);
                $user->setPassword($passwordHasher);
                $user->setRole(['ROLE_USER']);
                $user->setToken( bin2hex(random_bytes(16)));

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->sendVerificationEmail($user, $mailer);

                return $this->redirectToRoute('app_connexion');
            }
        }
        return $this->render('Register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function sendVerificationEmail(User $user, MailerInterface $mailer): void
    {
        $url = $this->generateUrl('app_verification_email', [
            'id' => $user->getId(),
            'token' => $user->getToken(), 
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = (new Email())
            ->from('3Dbazard@support.com')
            ->to($user->getEmail()) 
            ->subject('Vérification de l\'e-mail')
            ->html('<p>Merci de vérifier votre adresse e-mail en cliquant sur le lien suivant : <a href="' . $url . '">Lien de vérification</a></p>');

        $mailer->send($email);
    }
}
