<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add-users',
    description: 'Add an admin and a citizen account.',
)]
class AddUsersCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $usersAdded = 0;

        // 1. Ajouter l'Admin
        $adminEmail = 'admin@smartwaste.tn';
        $existingAdmin = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $adminEmail]);
        
        if (!$existingAdmin) {
            $admin = new Utilisateur();
            $admin->setEmail($adminEmail);
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPrenom('Super');
            $admin->setNom('Admin');
            $admin->setTelephone('+216 99 999 999');
            $admin->setVille('Tunis');
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $this->entityManager->persist($admin);
            $output->writeln("Compte Admin créé : admin@smartwaste.tn / admin123");
            $usersAdded++;
        } else {
            $output->writeln("Le compte Admin (admin@smartwaste.tn) existe déjà.");
            // Reset password just in case
            $existingAdmin->setPassword($this->passwordHasher->hashPassword($existingAdmin, 'admin123'));
            $output->writeln("Mot de passe admin réinitialisé à 'admin123'.");
            $usersAdded++;
        }

        // 2. Ajouter le Citoyen
        $citoyenEmail = 'citoyen@smartwaste.tn';
        $existingCitoyen = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $citoyenEmail]);
        
        if (!$existingCitoyen) {
            $citoyen = new Utilisateur();
            $citoyen->setEmail($citoyenEmail);
            $citoyen->setRoles(['ROLE_CITOYEN']);
            $citoyen->setPrenom('Ali');
            $citoyen->setNom('Ben Salah');
            $citoyen->setTelephone('+216 22 222 222');
            $citoyen->setVille('Ariana');
            $citoyen->setPassword($this->passwordHasher->hashPassword($citoyen, 'citoyen123'));
            $this->entityManager->persist($citoyen);
            $output->writeln("Compte Citoyen créé : citoyen@smartwaste.tn / citoyen123");
            $usersAdded++;
        } else {
            $output->writeln("Le compte Citoyen (citoyen@smartwaste.tn) existe déjà.");
            // Reset password just in case
            $existingCitoyen->setPassword($this->passwordHasher->hashPassword($existingCitoyen, 'citoyen123'));
            $output->writeln("Mot de passe citoyen réinitialisé à 'citoyen123'.");
            $usersAdded++;
        }

        if ($usersAdded > 0) {
            $this->entityManager->flush();
            $output->writeln("Tous les changements ont été sauvegardés en base de données.");
        }

        return Command::SUCCESS;
    }
}
