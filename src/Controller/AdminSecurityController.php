<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminSecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security_login")
     */
    public function login()
    {
        return $this->render("admin_security/index.html.twig");
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout(){

    }

}
