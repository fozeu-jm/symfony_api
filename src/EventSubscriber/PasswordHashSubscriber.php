<?php


namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Email\Mailer;
use App\Entity\User;
use App\Security\TokenGenerator;
use PhpParser\Lexer\TokenEmulator\TokenEmulatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordHashSubscriber implements EventSubscriberInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * PasswordHashSubscriber constructor.
     */
    public function __construct(
        Mailer $mailer,
        TokenGenerator $tokenGenerator,
        UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }


    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['registerUser', EventPriorities::PRE_WRITE]
        ];
    }

    public function registerUser(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        if (!$user instanceof User || !in_array($method, [Request::METHOD_POST])) {
            return;
        }
        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $user->getPassword())
        );
        $this->setConfirmationToken($user);

        //send confirmation email
        $this->mailer->sendConfirmationEmail($user);
    }

    public function setConfirmationToken(User $user)
    {
        $user->setConfirmationToken(
            $this->tokenGenerator->getRandomSecureToken()
        );
    }
}