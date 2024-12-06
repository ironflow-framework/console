<?php

namespace Forge\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateEnvCommand extends Command
{
   protected static $defaultName = 'env:generate';

   protected function configure(): void
   {
      $this
         ->setName(self::$defaultName)
         ->setDescription('Génère un fichier .env interactivement.');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $envPath = '.env';

      if (file_exists($envPath)) {
         $io->warning('.env existe déjà. Renommé en .env.backup.');
         rename($envPath, '.env.backup');
      }

      $database = $io->ask('Nom de la base de données', 'forge');
      $host = $io->ask('Hôte de la base de données', '127.0.0.1');
      $stripeKey = $io->ask('Clé Stripe API');
      $paypalKey = $io->ask('Clé PayPal API');

      $envContent = <<<EOT
        DB_DATABASE=$database
        DB_HOST=$host
        STRIPE_API_KEY=$stripeKey
        PAYPAL_API_KEY=$paypalKey
        EOT;

      file_put_contents($envPath, $envContent);

      $io->success('Fichier .env généré avec succès.');
      return Command::SUCCESS;
   }
}
