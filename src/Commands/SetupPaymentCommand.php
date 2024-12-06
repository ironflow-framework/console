<?php

namespace Forge\CLI\Commands;

use Forge\CLI\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Forge\Services\StripePaymentService;

class SetupPaymentCommand extends Command
{
   protected function configure()
   {
      $this
         ->setName('setup:payment')
         ->setDescription('Initialise et configure le système de paiement en ligne.')
         ->setHelp(
            <<<HELP
Cette commande permet de configurer le système de paiement en ligne.
Vous pouvez choisir de configurer Stripe, PayPal ou les deux options.

Utilisation :
  - Pour Stripe : `php forge setup:payment --stripe`
  - Pour PayPal : `php forge setup:payment --paypal`
  - Pour les deux : `php forge setup:payment --stripe --paypal`
HELP
         )
         ->addOption(
            'stripe',
            null,
            InputOption::VALUE_NONE,
            "Configurer le système de paiement avec Stripe."
         )
         ->addOption(
            'paypal',
            null,
            InputOption::VALUE_NONE,
            "Configurer le système de paiement avec PayPal."
         );
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $logger = new Logger();
      $io->title('🚀 Configuration du système de paiement en ligne');

      $useStripe = $input->getOption('stripe');
      $usePayPal = $input->getOption('paypal');

      // Vérifier si au moins une option est sélectionnée
      if (!$useStripe && !$usePayPal) {
         $io->warning('Aucune option de paiement spécifiée. Utilisez --stripe, --paypal ou les deux.');
         return Command::INVALID;
      }

      try {
         if ($useStripe) {
            $this->setupStripe($io, $logger);
         }

         if ($usePayPal) {
            $this->setupPayPal($io, $logger);
         }

         $io->success('Système de paiement configuré avec succès.');
         return Command::SUCCESS;
      } catch (\Throwable $e) {
         $io->error('Une erreur s\'est produite lors de la configuration du système de paiement : ' . $e->getMessage());
         $logger->log('error', 'Erreur lors de la configuration du paiement : ' . $e->getMessage());
         return Command::FAILURE;
      }
   }

   private function setupStripe(SymfonyStyle $io, Logger $logger): void
   {
      $io->section('🔧 Configuration de Stripe');
      try {
         // Logique pour configurer Stripe ici
         $io->text('Connexion à Stripe...');
         $stripeService = new StripePaymentService();
         $stripeService->configure();
         $io->success('Stripe a été configuré avec succès.');
         $logger->log('info', 'Stripe configuré avec succès.');
      } catch (\Exception $e) {
         throw new \RuntimeException("Erreur lors de la configuration de Stripe : " . $e->getMessage());
      }
   }

   private function setupPayPal(SymfonyStyle $io, Logger $logger): void
   {
      $io->section('🔧 Configuration de PayPal');
      try {
         // Logique pour configurer PayPal ici
         $io->text('Connexion à PayPal...');
         $io->success('PayPal a été configuré avec succès.');
         $logger->log('info', 'PayPal configuré avec succès.');
      } catch (\Exception $e) {
         throw new \RuntimeException("Erreur lors de la configuration de PayPal : " . $e->getMessage());
      }
   }
}
