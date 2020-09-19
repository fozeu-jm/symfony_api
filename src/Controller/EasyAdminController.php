<?php

namespace App\Controller;

use App\Controller\Admin\BlogPostCrudController;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EasyAdminController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();

        return $this->redirect($routeBuilder->setController(BlogPostCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('EasyAdmin');
    }



    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section("Blog Menu Section");
        yield MenuItem::linkToCrud('Posts', 'fa fa-file-pdf', BlogPost::class);
        yield MenuItem::linkToCrud('Comments', 'fa fa-commenting', Comment::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-user-circle', User::class);
        yield MenuItem::linkToCrud('Images', 'fa fa-camera', Image::class);

    }
}
