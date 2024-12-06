<?php

namespace Forge\CLI\Commands;

use Forge\CLI\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FileManager extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Génère des fichiers de type modèle, contrôleur, migration, factory ou seeder')
            ->addArgument('type', InputArgument::REQUIRED, 'Type de fichier')
            ->addArgument('name', InputArgument::REQUIRED)
            ->setHelp('Cette commande permet de générer des fichiers de type modèle, contrôleur, migration, factory ou seeder')
            ->addOption('migration', 'm', null, 'Généré la migration associé')
            ->addOption('controller', 'c', null, 'Généré le controller associé')
            ->addOption('factory', 'f', null, 'Généré la factory associé')
            ->addOption('seeder', 's', null, 'Généré le seeder associé');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        $fileName = $input->getArgument('name');

        switch ($type) {
            case 'model':
                $generateMigration = $input->getOption('migration');
                $generateController = $input->getOption('controller');
                $generateFactory = $input->getOption('factory');
                $generateSeeder = $input->getOption('seeder');

                $this->generate_model($fileName, $input, $output);
                if ($generateMigration) $this->generate_migration($fileName, $input, $output);
                if ($generateController) $this->generate_controller($fileName, $input, $output);
                if ($generateFactory) $this->generate_factory($fileName, $input, $output);
                if ($generateSeeder) $this->generate_seeder($fileName,  $input, $output);
                return Command::SUCCESS;

            case 'migration':
                $this->generate_migration($fileName, $input, $output);
                return Command::SUCCESS;

            case 'controller':
                $this->generate_controller($fileName, $input, $output);;
                return Command::SUCCESS;

            case 'factory':
                $this->generate_factory($fileName, $input, $output);
                return Command::SUCCESS;

            case 'seeder':
                $this->generate_seeder($fileName, $input, $output);
                return Command::SUCCESS;

            default:
                $io->error("Veuillez préciser le fichier à générer.");
                return Command::FAILURE;
        }
    }

    protected function generate_model($modelName, $input, $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Logger();
        try {
            $modelFile = __DIR__ . "/../../../app/Models/" . ucfirst($modelName) . ".php";
            $modelContent = "<?php\n\nnamespace App\Models;\n\nuse Forge\Database\Iron\Model;\n\nclass " . ucfirst($modelName) . " extends Model\n{\n    protected static \$table = '" . $this->getModelTable($modelName) . "';\n\n    // Logique du modèle ici\n}\n";

            file_put_contents($modelFile, $modelContent);
            $io->success("Modèle créé : " . $logger->bold($modelFile) . "");
            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $io->error("Une erreur s'est produite");
            return Command::FAILURE;
        }
    }

    protected function generate_factory($modelName, $input, $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Logger();
        try {
            $factoryFile = __DIR__ . "/../../../database/factories/" . ucfirst($modelName) . "Factory.php";
            $factoryContent = "<?php\n\nnamespace Database\Factories;\n\nuse App\\Models\\" . ucfirst($modelName) . ";\nuse Forge\Database\Factory;\nuse Faker\Generator as FakerGenerator;\n\nclass " . ucfirst($modelName) . "Factory extends Factory\n{\n    public function definition(FakerGenerator \$fake): array\n    {\n        return [\n            // Ajoutez ici les attributs à peupler avec Faker\n            //'column_name' => \$faker->word,\n        ];\n    }\n\n    /**\n     * Retourne la classe associée au modèle.\n     *\n     * @return string\n     */\n    protected function getModelClass(): string\n    {\n        return " . ucfirst($modelName) . "::class;\n    }\n}\n";

            file_put_contents($factoryFile, $factoryContent);
            $io->success("Factory créée : " . $logger->bold($factoryFile));
        } catch (\Throwable $th) {
            $io->error("Une erreur s'est produite");
        }
    }

    protected function generate_controller($controllerName, $input, $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Logger();
        try {
            $controllerFile = __DIR__ . "/../../../app/Controllers/" . ucfirst($controllerName) . "Controller.php";
            $controllerContent = "<?php\n\nnamespace App\Controllers;\n\nuse Forge\Http\Controller;\n\nclass " . ucfirst($controllerName) . "Controller extends Controller\n{\n    public function index()\n    {\n        // Logique pour lister les enregistrements\n    }\n    public function store()\n    {\n        // Logique pour créer un enregistrement\n    }\n}\n";

            file_put_contents($controllerFile, $controllerContent);
            $io->success("Contrôleur créé : " . $logger->bold($controllerFile));
            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $io->error("Une erreur s'est produite");
            return Command::FAILURE;
        }
    }

    protected function generate_migration($tableName, $input, $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Logger();
        try {
            if (str_ends_with($tableName, 'y')) {
                $tableName = str_replace('y', 'ie', $tableName);
            }
            // Nom et contenu du fichier de migration
            $timestamp = date('Y_m_d_His');
            $migrationFile = __DIR__ . "/../../../database/migrations/{$timestamp}_create_" . strtolower($tableName) . "s_table.php";

            $migrationContent = <<<PHP
            <?php

            use Forge\Database\Migration;
            use Forge\Database\Iron\Blueprint;
            use Forge\Database\Iron\Schema;

            return new class extends Migration
            {
                /**
                 * Exécute les migrations.
                 */
                public function up(): void
                {
                    Schema::create('{$tableName}s', function (Blueprint \$table) {
                        \$table->id();
                        \$table->timestamps();
                    });
                }

                /**
                 * Annule les migrations.
                 */
                public function down(): void
                {
                    Schema::dropIfExists('{$tableName}s');
                }
            };
            PHP;

            file_put_contents($migrationFile, $migrationContent);
            $io->success("Migration créé : " . $logger->bold($migrationFile));
            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $io->error("Une erreur s'est produite");
            return Command::FAILURE;
        }
    }

    protected function generate_seeder($seederName, $input, $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Logger();
        try {
            $seederFile = __DIR__ . "/../../../database/seeders/" . ucfirst($seederName) . "Seeder.php";
            $seederContent = "<?php\n\nnamespace Database\Seeders;\n\nuse Forge\Database\Seeder;\n\nclass " . ucfirst($seederName) . "Seeder extends Seeder\n{\n    public function run(): void\n    {\n        // Logique pour peupler la base de données\n    }\n}\n";

            file_put_contents($seederFile, $seederContent);
            $io->success("Seeder créé : " . $logger->bold($seederFile));
            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $io->error("Une erreur s'est produite");
            return Command::FAILURE;
        }
    }

    protected function generate_middleware($middlewareName, $input, $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Logger();

        try {
            $middlewareFile = __DIR__ .  "/../../../app/Middlewares/" . ucfirst($middlewareName) . "Middleware.php";
            $middlewareContent = "<?php\n\nnamespace App\Middlewares;\n\nuse Forge\Http\Middlewares;\nuse Symfony\Component\HttpFoundation\Request;\nuse Symfony\Component\HttpFoundation\Response;;\n\nclass " . ucfirst($middlewareName) . "Middleware extends AbstractMiddleware\n{\n    public function handle(Request \$request, callable \$next): Response\n    {\n        // Logique du middleware ici\n        return \$next();\\n    }\n";
            file_put_contents($middlewareFile, $middlewareContent);
            $io->success("Middleware créé : " . $logger->bold($middlewareFile));
            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $io->error("Une erreur s'est produite");
            return Command::FAILURE;
        }
    }

    protected function getModelTable($modelName)
    {
        if (str_ends_with($modelName, 'y')) {
            $modelName = str_replace('y', 'ie', $modelName);
        }
        return strtolower($modelName) . 's'; // Convention : le nom de la table est le nom du modèle au pluriel
    }
}
