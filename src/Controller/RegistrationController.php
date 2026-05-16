<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user = new Utilisateur();
            $user->setPrenom($request->request->get('prenom'));
            $user->setNom($request->request->get('nom'));
            $user->setEmail($request->request->get('email'));
            $user->setTelephone($request->request->get('tel'));
            $user->setVille($request->request->get('ville'));

            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password2');

            if ($password && $password === $passwordConfirm) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $password
                    )
                );

                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas ou sont invalides.');
            }
        }

        return $this->render('authentication/register.html.twig');
    }
}
