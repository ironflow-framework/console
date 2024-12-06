<?php

namespace Forge\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class ToggleMaintenanceCommand extends Command
{
   protected static $defaultName = 'maintenance';

   protected function configure(): void
   {
      $this
         ->setName(self::$defaultName)
         ->setDescription('Active ou désactive le mode maintenance')
         ->addArgument('state', InputArgument::REQUIRED, 'État du mode maintenance (on/off)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $state = $input->getArgument('state');

      $maintenanceFile = 'storage/maintenance.flag';

      if ($state === 'on') {
         file_put_contents($maintenanceFile, '1');
         $io->success('Mode maintenance activé.');
      } elseif ($state === 'off') {
         if (file_exists($maintenanceFile)) {
            unlink($maintenanceFile);
         }
         $io->success('Mode maintenance désactivé.');
      } else {
         $io->error('État invalide. Utilisez "on" ou "off".');
         return Command::FAILURE;
      }

      return Command::SUCCESS;
   }
}
