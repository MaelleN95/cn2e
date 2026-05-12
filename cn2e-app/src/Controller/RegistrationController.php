<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/devenir-membre', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $plainPassword = $form->get('plainPassword')->getData();

                // encoder le mot de passe et le stocker dans l'entité User
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                $user->setRoles(['ROLE_USER']);
                $user->setIsVerified(false);
                $user->setStatus(UserStatus::PENDING);

                $entityManager->persist($user);
                $entityManager->flush();

                // générer un token de confirmation d'email et envoyer l'email de confirmation
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address($_ENV['CONTACT_FROM'], 'CN2E'))
                        ->to((string) $user->getEmail())
                        ->subject('Confirmez votre adresse email')
                        ->htmlTemplate('emails/confirmation_email.html.twig')
                );

                $this->addFlash(
                    'success',
                    sprintf(
                        '
                        Votre compte a été créé avec succès.
                        Un email de confirmation vous a été envoyé.<br>
                        <a
                            href="%s"
                            class="mt-3 inline-flex text-sm font-medium underline hover:no-underline"
                        >
                            Vous n’avez pas reçu d’email ? Renvoyer un email
                        </a>
                        ',
                        $this->generateUrl(
                            'app_resend_verification_email',
                            ['id' => $user->getId()]
                        )
                    )
                );

                return $this->redirectToRoute('app_login');

            } catch (TransportExceptionInterface) {
                $this->addFlash(
                    'warning',
                    sprintf(
                        '
                        Votre compte est créé. Cependant, l’email de validation n’a pas pu être envoyé.<br>
                        <a
                            href="%s"
                            class="mt-3 inline-flex text-sm font-medium underline hover:no-underline"
                        >
                            Renvoyer un email
                        </a>
                        ',
                        $this->generateUrl(
                            'app_resend_verification_email',
                            ['id' => $user->getId()]
                        )
                    )
                );

                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('security/auth.html.twig', [
            'registrationForm' => $form,
            'activeForm' => 'register',
        ]);
    }

    #[Route('/verification/email/{id}', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager
    ): Response {
        $id = $request->attributes->get('id');

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash(
                'error',
                'Utilisateur non trouvé.'
            );

            return $this->redirectToRoute('app_register');
        }

        if ($user->isVerified()) {
            $this->addFlash(
                'info',
                'Votre adresse email est déjà vérifiée.'
            );

            return $this->redirectToRoute('app_login');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);

            // notifier le CN2E

        } catch (VerifyEmailExceptionInterface $exception) {

            $this->addFlash(
                'verify_email_error',
                $translator->trans($exception->getReason(), [], 'VerifyEmailBundle')
            );

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre adresse email a bien été vérifiée. Votre demande est maintenant en attente de validation par l’équipe du CN2E.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/verification/email/renvoyer/{id}', name: 'app_resend_verification_email')]
    public function resendVerificationEmail(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $id = $request->attributes->get('id');

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash(
                'error',
                'Utilisateur non trouvé.'
            );

            return $this->redirectToRoute('app_register');
        }

        if ($user->isVerified()) {
            $this->addFlash(
                'info',
                'Votre adresse email est déjà vérifiée.'
            );

            return $this->redirectToRoute('app_login');
        }

        try {

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address($_ENV['CONTACT_FROM'], 'CN2E'))
                    ->to((string) $user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            $this->addFlash(
                'success',
                'Un nouvel email de confirmation vous a été envoyé.'
            );

        } catch (TransportExceptionInterface) {
            $this->addFlash(
                'error',
                'Impossible d’envoyer l’email de confirmation. Nous sommes désolés. Veuillez réessayer plus tard.'
            );
        }

        return $this->redirectToRoute('app_login');
    }
}
