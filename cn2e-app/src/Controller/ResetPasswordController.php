<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Service\PasswordResetMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/mot-de-passe-oublie')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private PasswordResetMailer $passwordResetMailer,
    ) {
    }

    /**
     * Formulaire de demande de réinitialisation du mot de passe.
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $formData = [];

        $currentUser = $this->getUser();
        if ($currentUser instanceof User && $currentUser->getEmail()) {
            $formData['email'] = $currentUser->getEmail();
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail($email);
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Page de confirmation d'envoi d'email
     */
    #[Route('/email-envoye', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        $resetToken = $this->getTokenObjectFromSession();

        // Générer un jeton factice si l'utilisateur n'existe pas ou si quelqu'un a accédé directement à cette page.
        // Cela permet d'éviter de révéler si un utilisateur a été trouvé ou non à partir de l'adresse e-mail fournie
        if ($resetToken === null) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Vérifie et traite l'URL de réinitialisation sur laquelle 
     * l'utilisateur a cliqué dans son e-mail.
     */
    #[Route('/reinitialisation/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) {
            // Nous enregistrons le jeton dans la session et le supprimons de l'URL, afin d'éviter que celle-ci ne soit
            // chargée dans un navigateur et ne divulgue potentiellement le jeton à un script JavaScript tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException('Aucun jeton de réinitialisation du mot de passe n\'a été trouvé.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash(
                'reset_password_error', 
                'Lien invalide ou expiré.'
            );

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Le jeton est valide : autoriser l'utilisateur à modifier son mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Un jeton de réinitialisation de mot de passe ne doit être utilisé qu'une seule fois : il faut le supprimer.
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Hacher le mot de passe en clair, puis le définir.
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $this->entityManager->flush();

            // La session est supprimée une fois le mot de passe modifié.
            $this->cleanSessionAfterReset();

            $this->addFlash(
                'success', 
                'Votre mot de passe a été réinitialisé avec succès !'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Ne pas préciser si un compte utilisateur a été trouvé ou non.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $this->passwordResetMailer->send($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash(
                'reset_password_error',
                'Un email a déjà été envoyé récemment. Vérifiez votre boîte de réception.'
            );

            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->redirectToRoute('app_check_email');
    }
}
