<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Signalement;
use App\Entity\Poubelle;
use App\Entity\CategorieSignalement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/citoyen')]
class CitoyenController extends AbstractController
{
    // PARTIE DE dashboard CITOYEN 
    #[Route('/', name: 'app_citoyen_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $stats = [
            'total' => $entityManager->getRepository(Signalement::class)->count(['utilisateur' => $user]),
            'resolu' => $entityManager->getRepository(Signalement::class)->count(['utilisateur' => $user, 'statut' => 'Traité']),
            'en_cours' => $entityManager->getRepository(Signalement::class)->count(['utilisateur' => $user, 'statut' => 'En cours'])
        ];

        $recent_activity = $entityManager->getRepository(Notification::class)->findBy(['utilisateur' => $user], ['createdAt' => 'DESC'], 5);
        $poubelles = $entityManager->getRepository(Poubelle::class)->findAll();

        return $this->render('citoyen/pages/index.html.twig', [
            'stats' => $stats,
            'recent_activity' => $recent_activity,
            'poubelles' => $poubelles
        ]);
    }

    // PARTIE DE map CITOYEN
    #[Route('/map', name: 'app_citoyen_map')]
    public function map(EntityManagerInterface $entityManager): Response
    {
        $poubelles = $entityManager->getRepository(Poubelle::class)->findAll();
        return $this->render('citoyen/pages/map.html.twig', [
            'poubelles' => $poubelles
        ]);
    }

    // PARTIE DE SIGNAL CITOYEN
    #[Route('/signal', name: 'app_citoyen_signal')]
    public function signal(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(CategorieSignalement::class)->findAll();
        $poubelles = $entityManager->getRepository(Poubelle::class)->findAll();

        if ($request->isMethod('POST')) {
            $categorieId  = $request->request->get('type');
            $titre        = trim($request->request->get('titre', ''));
            $description  = trim($request->request->get('description', ''));
            $poubelleId   = $request->request->get('poubelle_id');
            $adresse      = trim($request->request->get('adresse', ''));
            $pieceJointe  = $request->files->get('piece_jointe');

            // ── Validation ──────────────────────────────────────────────────
            $errors = [];

            if (empty($categorieId)) {
                $errors[] = 'Veuillez sélectionner un type de problème.';
            }
            if (empty($titre)) {
                $errors[] = 'Veuillez saisir un titre pour votre signalement.';
            }
            if (empty($description)) {
                $errors[] = 'Veuillez saisir une description.';
            }
            if (empty($poubelleId) && empty($adresse)) {
                $errors[] = 'Veuillez sélectionner une poubelle sur la carte ou saisir une adresse.';
            }

            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->addFlash('error', $err);
                }
                return $this->render('citoyen/pages/signal.html.twig', [
                    'categories' => $categories,
                    'poubelles'  => $poubelles,
                ]);
            }

            // ── Build entity ─────────────────────────────────────────────────
            // Prepend address to description if no bin is selected
            if (empty($poubelleId) && !empty($adresse)) {
                $description = "📍 Localisation : " . $adresse . "\n\n" . $description;
            }

            $signalement = new Signalement();
            $signalement->setTitre($titre);
            $signalement->setDescription($description);
            $signalement->setStatut('En cours');
            $signalement->setUtilisateur($this->getUser());

            $categorie = $entityManager->getRepository(CategorieSignalement::class)->find($categorieId);
            if ($categorie) {
                $signalement->setCategorie($categorie);
            }

            if ($poubelleId) {
                $poubelle = $entityManager->getRepository(Poubelle::class)->find($poubelleId);
                if ($poubelle) {
                    $signalement->setPoubelle($poubelle);
                }
            }

            if ($pieceJointe && $pieceJointe->isValid()) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/signalements';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0777, true);
                }
                $filename = md5(uniqid()) . '.' . $pieceJointe->getClientOriginalExtension();
                try {
                    $pieceJointe->move($uploadsDir, $filename);
                    $signalement->setPhoto($filename);
                } catch (\Exception $e) {
                    // file upload failed silently – not blocking
                }
            }

            $entityManager->persist($signalement);
            $entityManager->flush();

            // ── Notify all admins ────────────────────────────────────────────
            $admins = $entityManager->getRepository(\App\Entity\Utilisateur::class)
                ->createQueryBuilder('u')
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%ROLE_ADMIN%')
                ->getQuery()
                ->getResult();

            foreach ($admins as $admin) {
                $notif = new \App\Entity\Notification();
                $notif->setUtilisateur($admin);
                $notif->setMessage(
                    "Nouveau signalement : \"" . $signalement->getTitre() . "\" soumis par " .
                    $this->getUser()->getPrenom() . " " . $this->getUser()->getNom() . "."
                );
                $entityManager->persist($notif);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Votre signalement a été envoyé avec succès. Nous reviendrons vers vous rapidement.');
            return $this->redirectToRoute('app_citoyen_mes_sig');
        }

        return $this->render('citoyen/pages/signal.html.twig', [
            'categories' => $categories,
            'poubelles'  => $poubelles,
        ]);
    }

    // PARTIE DE MES SIGNALEMENTS CITOYEN
    #[Route('/mes-signalements', name: 'app_citoyen_mes_sig')]
    public function mesSignalements(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $signalements = $entityManager->getRepository(Signalement::class)->findBy(['utilisateur' => $user], ['createdAt' => 'DESC']);
        return $this->render('citoyen/pages/mes-sig.html.twig', [
            'signalements' => $signalements
        ]);
    }

    #[Route('/messages', name: 'app_citoyen_messages')]
    public function messages(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $contenu = $request->request->get('contenu');
            if (!empty($contenu)) {
                $msg = new \App\Entity\Message();
                $msg->setExpediteur($user);
                // destinataire is null which implies it's for the system/admin
                $msg->setContenu($contenu);
                $entityManager->persist($msg);
                $entityManager->flush();

                return $this->redirectToRoute('app_citoyen_messages');
            }
        }

        // PARTIE DE MESSAGES CITOYEN
        $messages = $entityManager->getRepository(\App\Entity\Message::class)->createQueryBuilder('m')
            ->where('m.expediteur = :user OR m.destinataire = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('citoyen/pages/messages.html.twig', [
            'messages' => $messages
        ]);
    }

    // PARTIE DE NOTIFICATIONS CITOYEN
    #[Route('/notifications', name: 'app_citoyen_notifs')]
    public function notifications(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $notifications = $entityManager->getRepository(Notification::class)->findBy(['utilisateur' => $user], ['createdAt' => 'DESC']);
        
        $hasUnread = false;
        foreach ($notifications as $notif) {
            if (!$notif->isLue()) {
                $notif->setLue(true);
                $hasUnread = true;
            }
        }
        
        if ($hasUnread) {
            $entityManager->flush();
        }

        return $this->render('citoyen/pages/notifs.html.twig', [
            'notifications' => $notifications
        ]);
    }

    // PARTIE DE PROFIL CITOYEN
    #[Route('/profil', name: 'app_citoyen_profil')]
    public function profil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if ($request->isMethod('POST')) {
            $user->setPrenom($request->request->get('prenom'));
            $user->setNom($request->request->get('nom'));
            $user->setVille($request->request->get('ville'));
            $user->setTelephone($request->request->get('telephone'));
            
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_citoyen_profil');
        }

        return $this->render('citoyen/pages/profil.html.twig');
    }
}
