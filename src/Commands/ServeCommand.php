<?php

namespace Forge\CLI\Commands;

use Forge\CLI\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServeCommand extends Command
{
    protected $host = 'localhost';
    protected $port = 8000;
    protected function configure()
    {
        $this->setName('serve')
            ->setDescription('Lance le serveur PHP');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Logger();

        // Logique pour démarrer le serveur
        $io->title('🚀  TinyForge Framework');
        $io->text('Lancement du serveur PHP sur 127.0.0.1:8000...');

        $cmd = "php -S {$this->host}:{$this->port} -t public";

        // Vérifie que le port est disponible
        while (!$this->isPortAvailable($this->port)) {
            $logger->log("error", "Le port {$this->port} est déjà utilisé.");
            $io->text("Le port {$this->port} est déjà utilisé.");
            $this->port++;
            $io->text("Lancement sur le port: " . $this->port . "");
        }

        // Vérifie que le répertoire public existe
        if (!is_dir('public')) {
            $logger->log("error", "Le répertoire 'public' est introuvable. Assurez-vous qu'il existe.");
            $io->error("Le répertoire 'public' est introuvable. Assurez-vous qu'il existe.");
            return Command::FAILURE;
        }

        $process = proc_open($cmd, [
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ], $pipes);

        if (is_resource($process)) {
            $io->success('Le serveur a démarré avec succès.');
            $output->writeln('Le serveur est en marché à l\'adresse ' . $logger->bold("http://{$this->host}:{$this->port}"));
            $output->writeln("Appuyer Ctrl + C pour quitter");

            $logger->log("info", "Serveur demarré à l'addresse " . $logger->bold("http://{$this->host}:{$this->port}"));

            // Lire les logs du serveur et les afficher en temps réel
            while ($line = fgets($pipes[1])) {
                $io->writeln("<fg=green>$line</>");
            }

            fclose($pipes[1]);
            fclose($pipes[2]);

            // Garde le serveur actif
            proc_close($process);

            return Command::SUCCESS;
        }

        $io->error('Erreur lors du démarrage du serveur.');
        return Command::FAILURE;
    }

    /**
     * Vérifie si un port est disponible.
     *
     * @param int $port Le port à vérifier.
     * @return bool True si le port est disponible, False sinon.
     */
    private function isPortAvailable(int $port): bool
    {
        $connection = @fsockopen("localhost", $port);
        if ($connection) {
            fclose($connection);
            return false;
        }
        return true;
    }
}
