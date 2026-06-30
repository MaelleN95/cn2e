<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\SiteInformationAccessor;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        private SiteInformationAccessor $siteInformationAccessor,
    ) {
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $email = (new TemplatedEmail())
                ->from(new Address($this->siteInformationAccessor->getSenderEmail(),sprintf('Formulaire de contact %s', $this->siteInformationAccessor->getShortName())))
                ->replyTo(new Address($data['email'], $data['name']))
                ->to(new Address($_ENV['CONTACT_TO']))
                ->subject($data['objectMessage'])
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'name' => $data['name'],
                    'userEmail' => $data['email'],
                    'message' => $data['message'],
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}