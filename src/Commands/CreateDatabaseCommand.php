<?php

namespace Forge\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use PDO;
use PDOException;

class CreateDatabaseCommand extends Command
{
   protected static $defaultName = 'db:create';

   protected function configure(): void
   {
      $this
         ->setName(self::$defaultName)
         ->setDescription('Crée une nouvelle base de données et met à jour les paramètres dans le fichier .env.')
         ->setHelp('Cette commande permet de créer une base de données et de mettre à jour les paramètres de connexion.')
         ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Nom de la base de données à créer')
         ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Hôte de la base de données (par défaut : localhost)', 'localhost')
         ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'Utilisateur de la base de données', 'root')
         ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Mot de passe pour la base de données', '');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      // Récupération des options
      $dbName = $input->getOption('name');
      $dbHost = $input->getOption('host');
      $dbUser = $input->getOption('user');
      $dbPassword = $input->getOption('password');

      // Si aucun nom de base de données n'est précisé, on récupère la valeur depuis le fichier .env
      if (!$dbName) {
         $envParams = $this->getEnvParams();
         if (isset($envParams['DB_NAME']) && $envParams['DB_NAME']) {
            $dbName = $envParams['DB_NAME'];
            $dbHost = $envParams['DB_HOST'] ?? $dbHost;
            $dbUser = $envParams['DB_USER'] ?? $dbUser;
            $dbPassword = $envParams['DB_PASSWORD'] ?? $dbPassword;
         } else {
            $io->error('Le nom de la base de données est requis. Utilisez l\'option --name pour le spécifier ou assurez-vous que le fichier .env contient DB_NAME.');
            return Command::FAILURE;
         }
      }

      try {
         // Connexion au serveur MySQL
         $dsn = "mysql:host=$dbHost";
         $pdo = new PDO($dsn, $dbUser, $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         ]);

         // Création de la base de données
         $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
         $io->success("Base de données '$dbName' créée avec succès !");

         // Mise à jour du fichier .env
         $this->updateEnvFile([
            'DB_HOST' => $dbHost,
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPassword,
         ]);
         $io->success("Fichier .env mis à jour avec succès !");
         return Command::SUCCESS;
      } catch (PDOException $e) {
         $io->error("Erreur lors de la création de la base de données : " . $e->getMessage());
         return Command::FAILURE;
      }
   }

   private function getEnvParams(): array
   {
      $envPath = __DIR__ . '/../../../../.env'; // Chemin vers votre fichier .env
      if (!file_exists($envPath)) {
         throw new \Exception("Le fichier .env n'existe pas : $envPath");
      }

      // Chargement du fichier .env
      $envContent = file_get_contents($envPath);
      $envParams = [];

      // Recherche des variables DB_ dans le fichier .env
      preg_match_all('/^([A-Z_]+)=(.*)$/m', $envContent, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
         $envParams[$match[1]] = $match[2];
      }

      return $envParams;
   }

   private function updateEnvFile(array $data): void
   {
      $envPath = __DIR__ . '/../../../../.env'; // Chemin vers votre fichier .env
      if (!file_exists($envPath)) {
         throw new \Exception("Le fichier .env n'existe pas : $envPath");
      }

      $envContent = file_get_contents($envPath);
      foreach ($data as $key => $value) {
         $pattern = "/^$key=.*$/m";
         $replacement = "$key=$value";

         // Si la clé existe, on la remplace, sinon on l'ajoute
         if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, $replacement, $envContent);
         } else {
            $envContent .= "\n$replacement";
         }
      }

      file_put_contents($envPath, $envContent);
   }
}
