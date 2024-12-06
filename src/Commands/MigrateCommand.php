<?php

namespace Forge\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Forge\Database\Migration;

class MigrateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:migrate')
            ->setDescription('Effectue les migrations de la base de données')
            ->setHelp('Cette commande vous permet d\'exécuter des migrations, de faire un rollback, ou de rafraîchir les migrations.')
            // Ajouter les sous-commandes
            ->addOption('rollback', null, null, 'Annule les dernières migrations exécutées.')
            ->addOption('refresh', null, null, 'Fait un rollback et relance les migrations.')
            ->addOption('reset', null, null, 'Réinitialise toutes les migrations.')
            ->addOption('fresh', null, null, 'Supprime toutes les tables et relance les migrations.')
            ->addOption('status', null, null, 'Affiche l\'état des migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migration = new Migration();

        if ($input->getOption('rollback')) {
            $output->writeln('Exécution du rollback des migrations...');
            $migration->migrate('rollback');
        } elseif ($input->getOption('refresh')) {
            $output->writeln('Exécution du refresh des migrations...');
            $migration->migrate('refresh');
        } elseif ($input->getOption('reset')) {
            $output->writeln('Réinitialisation des migrations...');
            $migration->migrate('reset');
        } elseif ($input->getOption('fresh')) {
            $output->writeln('Exécution des migrations fraîches...');
            $migration->migrate('fresh');
        } elseif ($input->getOption('status')) {
            $output->writeln('Affichage de l\'état des migrations...');
            $migration->migrate('status');
        } else {
            // Par défaut, exécuter les migrations
            $output->writeln('Exécution des migrations...');
            $migration->migrate('run');
            
        }

        return Command::SUCCESS;
    }
}
