<?php

namespace Forge\CLI;

use Forge\CLI\Commands\CreateAdminUserCommand;
use Forge\CLI\Commands\CreateDatabaseCommand;
use Forge\CLI\Commands\FileManager;
use Forge\CLI\Commands\GenerateEnvCommand;
use Forge\CLI\Commands\GenerateKeyCommand;
use Symfony\Component\Console\Application;
use Forge\CLI\Commands\ServeCommand;
use Forge\CLI\Commands\MigrateCommand;
use Forge\CLI\Commands\ScaffoldCommand;
use Forge\CLI\Commands\SeedDatabaseCommand;
use Forge\CLI\Commands\SetupAuthCommand;
use Forge\CLI\Commands\SetupPaymentCommand;
use Forge\CLI\Commands\ToggleMaintenanceCommand;
use App\Models\User as UserModel;
use Forge\CLI\Commands\ExportDatabaseCommand;

class Console
{
    public function __construct()
    {
        // Application Console de Symfony
        $application = new Application();

        // Enregistrer les commandes
        $application->add(new CreateAdminUserCommand(UserModel::class));
        $application->add(new CreateDatabaseCommand());
        $application->add(new ExportDatabaseCommand());
        $application->add(new FileManager());
        $application->add(new GenerateKeyCommand());
        $application->add(new GenerateEnvCommand());
        $application->add(new MigrateCommand());
        $application->add(new ScaffoldCommand());
        $application->add(new SeedDatabaseCommand());
        $application->add(new ServeCommand());
        $application->add(new SetupAuthCommand());
        $application->add(new SetupPaymentCommand());
        $application->add(new ToggleMaintenanceCommand());

        // Exécution des commandes
        $application->run();
    }
}
