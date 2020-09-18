<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\UserConfirmation;
use App\Repository\UserRepository;
use App\Security\UserConfirmationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class UserConfirmationSubscriber implements EventSubscriberInterface
{

    /**
     * @var UserConfirmationService
     */
    private $userConfirmationService;

    public function __construct(
        UserConfirmationService $userConfirmationService
    )
    {
        $this->userConfirmationService = $userConfirmationService;
    }


    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE]
        ];
    }

    public function confirmUser(ViewEvent $event)
    {
        $request = $event->getRequest();

        if ('api_user_confirmations_post_collection' !==
            $request->get('_route')) {
            return;
        }

        /**
         * @var UserConfirmation $confirmationToken
         */
        $confirmationToken = $event->getControllerResult();

        $this->userConfirmationService->confirmUser(
            $confirmationToken->confirmationToken
        );

        $event->setResponse(new JsonResponse(null, JsonResponse::HTTP_OK));

    }
}