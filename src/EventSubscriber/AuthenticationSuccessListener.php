<?php


namespace App\EventSubscriber;


use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

//intercept authentication success listener to att token_ttl to authentication response
class AuthenticationSuccessListener
{
    /**
     * @var ContainerBuilder
     */
    private $token_ttl;

    /**
     * AuthenticationSuccessListener constructor.
     */
    public function __construct($token_ttl)
    {

        $this->token_ttl = $token_ttl;
    }


    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }
        $data['token_ttl']=$this->token_ttl;
        $event->setData($data);
    }

}