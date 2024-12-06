<?php

namespace Forge\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Models\User;

class CreateAdminUserCommand extends Command
{
   protected static $defaultName = 'user:create-admin';
   private User $userModel;

   public function __construct(User $userModel)
   {
      parent::__construct();
      $this->userModel = $userModel; // Injection de dépendance
   }

   protected function configure(): void
   {
      $this
         ->setName(self::$defaultName)
         ->setDescription('Crée un utilisateur administrateur')
         ->setHelp('Cette commande permet de créer un utilisateur avec le rôle administrateur.');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      $name = $io->ask('Nom de l\'administrateur');
      $email = $io->ask('Email de l\'administrateur', null, function ($value) {
         if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('L\'adresse email n\'est pas valide.');
         }
         return $value;
      });
      $password = $io->askHidden('Mot de passe');
      $confPassword = $io->askHidden('Confirmation du mot de passe');

      if ($password !== $confPassword) {
         $io->error('Les mots de passe ne correspondent pas.');
         return Command::FAILURE;
      }

      try {

         $user = $this->userModel::create($email, $name, $password, true, true);

         // Sauvegarde dans un fichier JSON (peut être adapté pour utiliser une base de données)
         $storagePath = 'storage/users.json';
         $existingUsers = file_exists($storagePath) ? json_decode(file_get_contents($storagePath), true) : [];
         $existingUsers[] = $user;
         file_put_contents($storagePath, json_encode($existingUsers, JSON_PRETTY_PRINT));

         $io->success("Administrateur '{$user['name']}' créé avec succès !");
         return Command::SUCCESS;
      } catch (\Exception $e) {
         $io->error('Une erreur est survenue lors de la création de l\'administrateur : ' . $e->getMessage());
         return Command::FAILURE;
      }
   }
}
