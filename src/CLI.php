<?php

namespace Forge\CLI;

use Forge\CLI\Commands\CommandInterface;

class CLI
{
    protected $commands = [];

    public function __construct()
    {
        // Initialiser la CLI ici
    }

    /**
     * Enregistre une commande dans la CLI.
     *
     * @param CommandInterface $command
     * @return void
     */
    public function registerCommand(CommandInterface $command): void
    {
        $this->commands[$command->getSignature()] = $command;
    }

    /**
     * Charge et enregistre les commandes d'un module externe.
     * Le module doit être enregistré dans composer.json comme une dépendance.
     *
     * @param string $moduleNamespace
     * @return void
     */
    public function loadExternalCommands(string $moduleNamespace): void
    {
        // Supposons que chaque module possède un fichier d'enregistrement des commandes
        $moduleCommandFile = __DIR__ . "/../../vendor/$moduleNamespace/cli/commands.php";

        if (file_exists($moduleCommandFile)) {
            include $moduleCommandFile;
        }
    }

    public function runCommand(string $commandSignature): void
    {
        if (isset($this->commands[$commandSignature])) {
            $this->commands[$commandSignature]->handle();
        } else {
            echo "Commande inconnue : $commandSignature\n";
        }
    }

    /**
     * Affiche toutes les commandes disponibles.
     */
    public function showAvailableCommands(): void
    {
        foreach ($this->commands as $signature => $command) {
            echo "Commande : $signature\n";
        }
    }
}
