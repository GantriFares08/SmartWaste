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
    name: 'app:create-citoyen',
    description: 'Creates a default citoyen user',
)]
class CreateCitoyenCommand extends Command
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

        $email = 'citoyen@smart.tn';
        $password = 'citoyen123';

        $existingUser = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->warning('User citoyen@smart.tn already exists.');
            return Command::FAILURE;
        }

        $user = new Utilisateur();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setNom('Ayedi');
        $user->setPrenom('Sami');
        $user->setTelephone('87654321');
        $user->setVille('Sousse');

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Citoyen user created successfully! Email: %s, Password: %s', $email, $password));

        return Command::SUCCESS;
    }
}
