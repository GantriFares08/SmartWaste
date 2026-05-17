<?php

namespace App\Command;

use App\Entity\CategorieSignalement;
use App\Entity\CategoriePoubelle;
use App\Entity\Notification;
use App\Entity\Poubelle;
use App\Entity\Signalement;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-fixtures',
    description: 'Charge des données de test (fake data) dans la base de données',
)]
class LoadFixturesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $citoyen = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => 'citoyen@smart.tn']);
        if (!$citoyen) {
            $io->error('Utilisateur citoyen@smart.tn non trouvé. Lancez php bin/console app:create-citoyen d\'abord.');
            return Command::FAILURE;
        }

        // 1. Catégories de Poubelle
        $catPNames = ['Plastique', 'Papier', 'Verre', 'Organique'];
        $categoriesP = [];
        foreach ($catPNames as $name) {
            $cat = new CategoriePoubelle();
            $cat->setNom($name);
            $this->entityManager->persist($cat);
            $categoriesP[] = $cat;
        }

        // 2. Poubelles
        $locations = [
            ['P-B-001', 36.8002, 10.1857],
            ['P-B-002', 36.7985, 10.1812],
            ['P-B-003', 36.8050, 10.1830],
            ['P-G-001', 36.8950, 10.1870],
        ];
        $poubelles = [];
        foreach ($locations as $loc) {
            $p = new Poubelle();
            $p->setReference($loc[0]);
            $p->setLatitude($loc[1]);
            $p->setLongitude($loc[2]);
            $p->setCategorie($categoriesP[array_rand($categoriesP)]);
            $this->entityManager->persist($p);
            $poubelles[] = $p;
        }

        // 3. Catégories de Signalement
        $catSNames = ['Poubelle pleine', 'Déversement illégal', 'Poubelle endommagée', 'Odeurs nuisibles'];
        $categoriesS = [];
        foreach ($catSNames as $name) {
            $cat = new CategorieSignalement();
            $cat->setNom($name);
            $this->entityManager->persist($cat);
            $categoriesS[] = $cat;
        }

        // 4. Signalements
        for ($i = 1; $i <= 12; $i++) {
            $sig = new Signalement();
            $sig->setTitre('Signalement #' . str_pad($i, 3, '0', STR_PAD_LEFT));
            $sig->setDescription('Ceci est une description générée automatiquement pour le test du signalement numéro ' . $i);
            $sig->setStatut($i % 3 == 0 ? 'Traité' : ($i % 2 == 0 ? 'En cours' : 'Nouveau'));
            $sig->setUtilisateur($citoyen);
            $sig->setCategorie($categoriesS[array_rand($categoriesS)]);
            $sig->setPoubelle($poubelles[array_rand($poubelles)]);
            $this->entityManager->persist($sig);
        }

        // 5. Notifications
        for ($i = 1; $i <= 8; $i++) {
            $notif = new Notification();
            $notif->setMessage('Nouvelle notification système #' . $i);
            $notif->setUtilisateur($citoyen);
            $this->entityManager->persist($notif);
        }

        $this->entityManager->flush();

        $io->success('Données de test (Fixtures) chargées avec succès !');

        return Command::SUCCESS;
    }
}
