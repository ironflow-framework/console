<?php

namespace Forge\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Database\Seeders\SeederManager;

class SeedDatabaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:seed')
            ->setDescription('Exécute le seeding de la base de données pour un seeder spécifique ou tous les seeders.')
            ->setHelp(
                <<<HELP
Cette commande permet d'exécuter les seeders pour initialiser ou enrichir la base de données avec des données fictives ou par défaut.

Options :
- Utilisez l'option `--seeder` pour exécuter un seeder spécifique.
- Si aucune option n'est fournie, tous les seeders seront exécutés.
HELP
            )
            ->addOption(
                'seeder',
                'S',
                InputOption::VALUE_REQUIRED,
                'Nom d\'un seeder spécifique à exécuter.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $seederName = $input->getOption('seeder');

        try {
            if ($seederName) {
                $this->runSpecificSeeder($seederName, $io);
            } else {
                $this->runAllSeeders($io);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Une erreur s'est produite pendant le seeding : " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function runSpecificSeeder(string $seederName, SymfonyStyle $io): void
    {
        $seederClass = "Database\\Seeders\\" . ucfirst($seederName) . "Seeder";

        if (!class_exists($seederClass)) {
            throw new \RuntimeException("Le seeder spécifié '$seederClass' n'existe pas.");
        }

        $seederInstance = new $seederClass();

        if (!method_exists($seederInstance, 'run')) {
            throw new \RuntimeException("Le seeder '" . get_class($seederInstance) . "' ne contient pas de méthode 'run'.");
        }

        $io->section("Exécution du seeder : {$seederName}");
        $seederInstance->run();
        $io->success("Seeder {$seederName} exécuté avec succès.");
    }

    private function runAllSeeders(SymfonyStyle $io): void
    {
        $io->section('Exécution de tous les seeders...');
        $seederManager = new SeederManager();

        if (!method_exists($seederManager, 'run')) {
            throw new \RuntimeException("La classe SeederManager ne contient pas de méthode 'run'.");
        }

        $seederManager->run();
        $io->success('Tous les seeders ont été exécutés avec succès.');
    }
}
