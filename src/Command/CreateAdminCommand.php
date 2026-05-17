<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a default admin user',
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = 'admin@smart.tn';
        $password = 'admin123';

        $existingUser = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->warning('User admin@smart.tn already exists.');
            return Command::FAILURE;
        }

        $user = new Utilisateur();
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setNom('Khalil');
        $user->setPrenom('Mohamed');
        $user->setTelephone('12345678');
        $user->setVille('Tunis');

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user created successfully! Email: %s, Password: %s', $email, $password));

        return Command::SUCCESS;
    }
}
