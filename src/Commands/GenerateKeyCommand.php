<?php

namespace Forge\CLI\Commands;

use Forge\Support\Helpers\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateKeyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('key:generate')
            ->setDescription('Génère une nouvelle clé secrète pour l\'application')
            ->setHelp('Cette command permet de créer une clé secrète pour l\'application');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = Str::random(32); // Génère une clé secrète de 32 caractères aléatoires

        $io = new SymfonyStyle($input, $output);

        $envFile = realpath(__DIR__ . '/../../.env');
        if (file_exists($envFile)) {
            file_put_contents($envFile, str_replace(
                'APP_KEY=' . $this->getAppKeyFromEnv(), // Remplace la clé existante si elle est déjà définie
                'APP_KEY=' . $key,
                file_get_contents($envFile)
            ));
            $io->info('Clé générée avec succès !');
            return Command::SUCCESS;
        } else {
            $io->error('Le fichier .env n\'existe pas.');
            return Command::FAILURE;
        }
    }

    // Récupère la clé actuelle définie dans le fichier .env
    private function getAppKeyFromEnv()
    {
        $envFile = realpath(__DIR__ . '/../../.env');;
        $contents = file_get_contents($envFile);

        if (preg_match('/^APP_KEY=(.*)$/m', $contents, $matches)) {
            return $matches[0];
        }

        return '';
    }
}
