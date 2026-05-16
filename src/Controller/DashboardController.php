<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function adminDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return new Response('<html><body><h1>Tableau de bord Administrateur</h1><p>Bienvenue dans l\'espace admin.</p><a href="/logout">Déconnexion</a></body></html>');
    }

    #[Route('/citoyen/dashboard', name: 'app_user_dashboard')]
    public function userDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return new Response('<html><body><h1>Tableau de bord Citoyen</h1><p>Bienvenue dans l\'espace citoyen.</p><a href="/logout">Déconnexion</a></body></html>');
    }
}
