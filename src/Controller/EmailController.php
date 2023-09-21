<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailController extends AbstractController
{
    #[Route('/api/send-email', name: 'app_send_email')]
    public function index(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('votre_email@example.com') // Remplacez par votre adresse e-mail
            ->to('adresse_destinataire@example.com') // Remplacez par l'adresse e-mail du destinataire
            ->subject('Sujet de l\'e-mail')
            ->html('<p>Contenu de l\'e-mail</p>');

        $mailer->send($email);

        return $this->render('email/index.html.twig', [
            'controller_name' => 'EmailController',
        ]);
    }
}

