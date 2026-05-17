<?php
require_once __DIR__.'/vendor/autoload_runtime.php';

use App\Kernel;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
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
    return $kernel;
};
