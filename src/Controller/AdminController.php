<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Entity\Utilisateur;
use App\Entity\Poubelle;
use App\Entity\CategorieSignalement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $stats = [
            'total_signalements' => $entityManager->getRepository(Signalement::class)->count([]),
            'active_sensors' => $entityManager->getRepository(Poubelle::class)->count([]),
            'total_users' => $entityManager->getRepository(Utilisateur::class)->count([]),
            'efficiency' => '89%'
        ];

        $recent_signalements = $entityManager->getRepository(Signalement::class)->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/pages/index.html.twig', [
            'stats' => $stats,
            'recent_signalements' => $recent_signalements
        ]);
    }

    #[Route('/map', name: 'app_admin_map')]
    public function map(EntityManagerInterface $entityManager): Response
    {
        $poubelles = $entityManager->getRepository(Poubelle::class)->findAll();
        return $this->render('admin/pages/map.html.twig', [
            'poubelles' => $poubelles
        ]);
    }

    #[Route('/signalements', name: 'app_admin_signalements')]
    public function signalements(EntityManagerInterface $entityManager): Response
    {
        $signalements = $entityManager->getRepository(Signalement::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/pages/signalements.html.twig', [
            'signalements' => $signalements
        ]);
    }

    #[Route('/messages', name: 'app_admin_messages')]
    public function messages(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Get all citizens (to list them in the conversations sidebar)
        $citizens = $entityManager->getRepository(Utilisateur::class)->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_USER%')
            ->getQuery()
            ->getResult();

        // 2. Determine active citizen
        $activeCitizenId = $request->query->get('citoyen');
        $activeCitizen = null;
        if ($activeCitizenId) {
            $activeCitizen = $entityManager->getRepository(Utilisateur::class)->find($activeCitizenId);
        }

        // If no active citizen is explicitly specified, select the first citizen in the list
        if (!$activeCitizen && !empty($citizens)) {
            $activeCitizen = $citizens[0];
        }

        // 3. Handle sending a reply message
        if ($activeCitizen && $request->isMethod('POST')) {
            $contenu = $request->request->get('contenu');
            if (!empty($contenu)) {
                $msg = new \App\Entity\Message();
                $msg->setExpediteur($this->getUser());
                $msg->setDestinataire($activeCitizen);
                $msg->setContenu($contenu);
                $entityManager->persist($msg);
                $entityManager->flush();

                return $this->redirectToRoute('app_admin_messages', ['citoyen' => $activeCitizen->getId()]);
            }
        }

        // 4. Load messages for the active conversation
        $messages = [];
        if ($activeCitizen) {
            $messages = $entityManager->getRepository(\App\Entity\Message::class)->createQueryBuilder('m')
                ->where('(m.expediteur = :admin AND m.destinataire = :citoyen) OR (m.expediteur = :citoyen AND (m.destinataire = :admin OR m.destinataire IS NULL))')
                ->setParameter('admin', $this->getUser())
                ->setParameter('citoyen', $activeCitizen)
                ->orderBy('m.createdAt', 'ASC')
                ->getQuery()
                ->getResult();
            
            // Mark received messages as read
            foreach ($messages as $msg) {
                if ($msg->getExpediteur() === $activeCitizen && !$msg->isLu()) {
                    $msg->setLu(true);
                }
            }
            $entityManager->flush();
        }

        // 5. Get last message for each citizen to display in the conversation list
        $citizensWithLastMsg = [];
        foreach ($citizens as $c) {
            $lastMsg = $entityManager->getRepository(\App\Entity\Message::class)->createQueryBuilder('m')
                ->where('(m.expediteur = :c) OR (m.destinataire = :c)')
                ->setParameter('c', $c)
                ->orderBy('m.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
            $citizensWithLastMsg[] = [
                'entity' => $c,
                'lastMessage' => $lastMsg ? $lastMsg->getContenu() : 'Aucune discussion',
                'unreadCount' => $entityManager->getRepository(\App\Entity\Message::class)->count([
                    'expediteur' => $c,
                    'destinataire' => null,
                    'lu' => false
                ])
            ];
        }

        return $this->render('admin/pages/messages.html.twig', [
            'citizens' => $citizensWithLastMsg,
            'activeCitizen' => $activeCitizen,
            'messages' => $messages
        ]);
    }

    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(Utilisateur::class)->findAll();
        return $this->render('admin/pages/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/add-admin', name: 'app_admin_add_admin')]
    public function addAdmin(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user = new Utilisateur();
            $user->setPrenom($request->request->get('prenom'));
            $user->setNom($request->request->get('nom'));
            $user->setEmail($request->request->get('email'));
            $user->setRoles([$request->request->get('roles')]);

            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if ($password && $password === $passwordConfirm) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Nouvel administrateur ajouté avec succès.');
                return $this->redirectToRoute('app_admin_users');
            } else {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            }
        }

        return $this->render('admin/pages/add_admin.html.twig');
    }

    #[Route('/user/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(int $id, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($request->isMethod('POST')) {
            $user->setPrenom($request->request->get('prenom'));
            $user->setNom($request->request->get('nom'));
            $user->setEmail($request->request->get('email'));
            $user->setRoles([$request->request->get('roles')]);

            $password = $request->request->get('password');
            if ($password) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/pages/edit_user.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/{id}/delete', name: 'app_admin_user_delete')]
    public function deleteUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Utilisateur::class)->find($id);
        if ($user) {
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            } else {
                // Delete related notifications
                $notifications = $entityManager->getRepository(\App\Entity\Notification::class)->findBy(['utilisateur' => $user]);
                foreach ($notifications as $notification) {
                    $entityManager->remove($notification);
                }

                // Delete related signalements
                $signalements = $entityManager->getRepository(Signalement::class)->findBy(['utilisateur' => $user]);
                foreach ($signalements as $signalement) {
                    $entityManager->remove($signalement);
                }

                // Delete related messages
                $messages = $entityManager->getRepository(\App\Entity\Message::class)->createQueryBuilder('m')
                    ->where('m.expediteur = :user OR m.destinataire = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getResult();
                foreach ($messages as $msg) {
                    $entityManager->remove($msg);
                }

                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('success', 'Utilisateur supprimé avec succès.');
            }
        } else {
            $this->addFlash('error', 'Utilisateur introuvable.');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/signalement/{id}/valider', name: 'app_admin_signalement_valider', methods: ['POST', 'GET'])]
    public function validerSignalement(int $id, EntityManagerInterface $entityManager): Response
    {
        $signalement = $entityManager->getRepository(Signalement::class)->find($id);
        if ($signalement) {
            $signalement->setStatut('Traité');
            
            // Create notification for the user who reported it
            if ($signalement->getUtilisateur()) {
                $notification = new \App\Entity\Notification();
                $notification->setUtilisateur($signalement->getUtilisateur());
                $notification->setMessage("Votre signalement #" . $signalement->getId() . " (" . $signalement->getTitre() . ") a été validé et marqué comme 'Traité' par l'administration.");
                $entityManager->persist($notification);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Le signalement a été validé avec succès.');
        } else {
            $this->addFlash('error', 'Signalement introuvable.');
        }

        return $this->redirectToRoute('app_admin_signalements');
    }

    #[Route('/signalement/{id}/assigner', name: 'app_admin_signalement_assigner', methods: ['POST', 'GET'])]
    public function assignerSignalement(int $id, EntityManagerInterface $entityManager): Response
    {
        $signalement = $entityManager->getRepository(Signalement::class)->find($id);
        if ($signalement) {
            $signalement->setStatut('En cours');
            
            // Create notification for the user who reported it
            if ($signalement->getUtilisateur()) {
                $notification = new \App\Entity\Notification();
                $notification->setUtilisateur($signalement->getUtilisateur());
                $notification->setMessage("Votre signalement #" . $signalement->getId() . " (" . $signalement->getTitre() . ") est désormais pris en charge par nos équipes de terrain.");
                $entityManager->persist($notification);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Le signalement a été mis en cours de traitement.');
        } else {
            $this->addFlash('error', 'Signalement introuvable.');
        }

        return $this->redirectToRoute('app_admin_signalements');
    }

    #[Route('/reports', name: 'app_admin_reports')]
    public function reports(EntityManagerInterface $entityManager): Response
    {
        $signalements = $entityManager->getRepository(Signalement::class)->findAll();
        
        $stats = [
            'total' => count($signalements),
            'traite' => count(array_filter($signalements, fn($s) => $s->getStatut() === 'Traité')),
            'en_cours' => count(array_filter($signalements, fn($s) => $s->getStatut() === 'En cours')),
        ];
        
        $categoriesStats = [];
        foreach ($signalements as $s) {
            $catName = $s->getCategorie() ? $s->getCategorie()->getNom() : 'Inconnu';
            if (!isset($categoriesStats[$catName])) {
                $categoriesStats[$catName] = 0;
            }
            $categoriesStats[$catName]++;
        }

        return $this->render('admin/pages/reports.html.twig', [
            'stats' => $stats,
            'categoriesStats' => $categoriesStats
        ]);
    }

    #[Route('/notifications', name: 'app_admin_notifs')]
    public function notifications(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $notifications = $entityManager->getRepository(\App\Entity\Notification::class)->findBy(['utilisateur' => $user], ['createdAt' => 'DESC']);
        
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

        return $this->render('admin/pages/notifs.html.twig', [
            'notifications' => $notifications
        ]);
    }

    #[Route('/settings', name: 'app_admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/pages/settings.html.twig');
    }

    #[Route('/reset-data', name: 'app_admin_reset_data')]
    public function resetData(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $conn = $entityManager->getConnection();
        
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $conn->executeStatement('TRUNCATE TABLE message');
        $conn->executeStatement('TRUNCATE TABLE notification');
        $conn->executeStatement('TRUNCATE TABLE signalement');
        $conn->executeStatement('TRUNCATE TABLE poubelle');
        $conn->executeStatement('TRUNCATE TABLE categorie_signalement');
        $conn->executeStatement('DELETE FROM utilisateur WHERE id != :id', ['id' => $this->getUser()->getId()]);
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $categoriesNames = ['Déchets encombrants', 'Poubelle pleine', 'Déchets dangereux', 'Déchets verts', 'Déchets électroniques', 'Autre'];
        $categories = [];
        foreach ($categoriesNames as $name) {
            $cat = new CategorieSignalement();
            $cat->setNom($name);
            $entityManager->persist($cat);
            $categories[] = $cat;
        }
        $entityManager->flush();
        
        $usersData = [
            ['nom' => 'Citoyen', 'prenom' => 'Utilisateur', 'email' => 'user@smart.tn', 'ville' => 'Tunis', 'telephone' => '55123456'],
        ];
        $users = [];
        foreach ($usersData as $data) {
            $u = new Utilisateur();
            $u->setNom($data['nom']);
            $u->setPrenom($data['prenom']);
            $u->setEmail($data['email']);
            $u->setVille($data['ville']);
            $u->setTelephone($data['telephone']);
            $u->setRoles(['ROLE_USER']);
            $u->setPassword($passwordHasher->hashPassword($u, 'citoyen123'));
            $entityManager->persist($u);
            $users[] = $u;
        }

        $poubellesData = [
            ['ref' => 'P-TUN-001', 'lat' => 36.8065, 'lng' => 10.1815, 'cat' => 1],
            ['ref' => 'P-TUN-002', 'lat' => 36.8125, 'lng' => 10.1785, 'cat' => 1],
            ['ref' => 'P-SFX-001', 'lat' => 34.7400, 'lng' => 10.7600, 'cat' => 0],
            ['ref' => 'P-SOU-001', 'lat' => 35.8256, 'lng' => 10.6369, 'cat' => 2],
        ];
        $poubelles = [];
        foreach ($poubellesData as $data) {
            $p = new Poubelle();
            $p->setReference($data['ref']);
            $p->setLatitude($data['lat']);
            $p->setLongitude($data['lng']);
            $p->setCategorie($categories[$data['cat']]);
            $entityManager->persist($p);
            $poubelles[] = $p;
        }
        
        $entityManager->flush();

        $signalementsData = [
            ['titre' => 'Poubelle débordante', 'desc' => 'La poubelle est pleine depuis 3 jours, les déchets s\'accumulent sur le trottoir.', 'statut' => 'En cours', 'u' => 0, 'p' => 0, 'c' => 1],
            ['titre' => 'Meubles abandonnés', 'desc' => 'Quelqu\'un a laissé un vieux canapé et une table au coin de la rue.', 'statut' => 'Traité', 'u' => 0, 'p' => null, 'c' => 0],
            ['titre' => 'Déversement suspect', 'desc' => 'Des bidons d\'huile moteur abandonnés près du port.', 'statut' => 'En cours', 'u' => 0, 'p' => 2, 'c' => 2],
            ['titre' => 'Amas de branches', 'desc' => 'Suite à l\'élagage, les branches bloquent le passage piéton.', 'statut' => 'Traité', 'u' => 0, 'p' => null, 'c' => 3],
            ['titre' => 'Piles et batteries', 'desc' => 'Un grand nombre de piles usagées déposées dans la poubelle normale.', 'statut' => 'En cours', 'u' => 0, 'p' => 3, 'c' => 4],
        ];

        foreach ($signalementsData as $data) {
            $s = new Signalement();
            $s->setTitre($data['titre']);
            $s->setDescription($data['desc']);
            $s->setStatut($data['statut']);
            $s->setUtilisateur($users[$data['u']]);
            if ($data['p'] !== null) {
                $s->setPoubelle($poubelles[$data['p']]);
            }
            $s->setCategorie($categories[$data['c']]);
            $entityManager->persist($s);
        }

        $entityManager->flush();

        $this->addFlash('success', 'La base de données a été réinitialisée avec succès avec de nouvelles données de démonstration.');
        return $this->redirectToRoute('app_admin_dashboard');
    }
}
