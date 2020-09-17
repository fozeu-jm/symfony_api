<?php


namespace App\Handler;


use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $tokenManager;
    /**
     * @var AuthenticationManagerInterface
     */

    public function __construct(
        HttpUtils $httpUtils,
        HttpKernelInterface $httpKernel,
        JWTTokenManagerInterface $tokenManager,
        LoggerInterface $logger = null)
    {
        parent::__construct($httpUtils);
        $this->tokenManager = $tokenManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {

        $tokenCode = $this->tokenManager->create($token->getUser());

        return new JsonResponse([
            "token" => $tokenCode,
            "token_ttl" => $this->ttl
        ]);
    }
}