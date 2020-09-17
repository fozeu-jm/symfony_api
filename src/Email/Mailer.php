<?php


namespace App\Email;


use App\Entity\User;
use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        \Swift_Mailer $mailer,
        Environment $twig
    )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @param User $user
     */
    public function sendConfirmationEmail(User $user)
    {
        $body = $this->twig->render(
            'email/confirmation.html.twig',
            [
                'mailUser' => $user
            ]
        );

        $message = (new Swift_Message('Please confirm your account !'))
            ->setFrom('fozeu.jm@gmail.com')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    public function sendAccountActivationSuccessMessage(User $user){
        $body = $this->twig->render(
            'email/confirmation_user.html.twig',
            [
                'mailUser' => $user
            ]
        );

        $message = (new Swift_Message('Account Activated !'))
            ->setFrom('fozeu.jm@gmail.com')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }
}