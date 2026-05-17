<?php
use App\Kernel;
use App\Entity\Utilisateur;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$hasher = $container->get('security.user_password_hasher');

$user = $em->getRepository(Utilisateur::class)->findOneBy(['email' => 'admin@smart.tn']);
if ($user) {
    $user->setPassword($hasher->hashPassword($user, 'admin123'));
    $em->flush();
    echo "Admin password reset successfully to 'admin123'\n";
} else {
    echo "Admin user not found\n";
}
