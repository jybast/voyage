<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    /**
     * Affiche et execute une demande pour changer un mot de passe.
     *
     * @Route("", name="blog_forgot_password_request")
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {
        // Instance de formulaire
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        // traitement du formulaire et envoi d'un mail
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer
            );
        }

        // Si j'ai un utilisateur connecté
        if ($this->getUser()) {
            return $this->render('user/modifierPassword.html.twig', [
                'requestForm' => $form->createView(),
            ]);
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation après une demande.
     *
     * @Route("/check-email", name="blog_check_email")
     */
    public function checkEmail(): Response
    {
        // Empêche les utilisteurs d'accéder directement à cette page
        if (!$this->canCheckEmail()) {
            return $this->redirectToRoute('blog_forgot_password_request');
        }

        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }

    /**
     * Valide et traite l'URL cliquée dans le mail.
     *
     * @Route("/reset/{token}", name="blog_reset_password")
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
    {
        if ($token) {
            // On garde le token en session et on le supprime de l'URL, pour éviter
            // d'être liée par la suite.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('blog_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('Le token du mot de passe non trouvé dans la session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'Votre demande ne peut pas être traitée - %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('blog_forgot_password_request');
        }

        // Le token est valide; l'utilisateur peut modifier son mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Un token ne doit être utilisé qu'une seule fois, le supprimer.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode le mot de passe en clair.
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            // La session est mise à jour après le changement de mot de passe.
            $this->cleanSessionAfterReset();

            // redirige vers la page accueil
            return $this->redirectToRoute('blog_accueil');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Indique qu'on peut voir la page blog_check_email page.
        $this->setCanCheckEmailInSession();

        // Ne révèle pas si un compte a été trouvé ou non.
        if (!$user) {
            return $this->redirectToRoute('blog_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));

            return $this->redirectToRoute('blog_check_email');
        }

        // Construction du mail
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@jymaerca.fr', 'Blog voyage passion'))
            ->to($user->getEmail())
            ->subject('Votre demande de changement de mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
            ])
        ;
        // envoi du mail
        $mailer->send($email);

        // renvoi sur la page demandant de vérifier les mails reçus
        return $this->redirectToRoute('blog_check_email');
    }
}
