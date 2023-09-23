<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends AbstractController
{
    #[Route('/api/verification-email/{id}/{token}', name: 'app_verification_email')]
    public function verifyEmail(EntityManagerInterface $entityManager, UserRepository $userRepository, $id, $token): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);

        if ($user->isActive() != true) {
            $id = "";
            $token = "";
            throw $this->createNotFoundException('Votre compte à été désactivé vous ne pourrez pas valider votre adresse email.');
        }

        if (!$user || $user->getToken() !== $token ) {
            throw $this->createNotFoundException('Lien de vérification invalide');
        }

        $user->setIsVerified(true);
        $user->setToken(null);

        $entityManager->flush();

        return $this->redirectToRoute('app_connexion');
    }
}
