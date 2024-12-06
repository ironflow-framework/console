<?php

namespace Forge\CLI\Commands;

use Forge\CLI\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScaffoldCommand extends Command
{
   protected function configure()
   {
      $this
         ->setName('scaffold')
         ->setDescription('Génère un modèle, un contrôleur, une migration et des routes CRUD.')
         ->addArgument('name', InputArgument::REQUIRED, "Nom du modèle (singulier).")
         ->addOption('migration', null, null, 'Inclure une migration dans la génération')
         ->addOption('controller', null, null, 'Inclure un contrôleur dans la génération');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $logger = new Logger();

      $modelName = ucfirst($input->getArgument('name'));
      $migrationOption = $input->getOption('migration');
      $controllerOption = $input->getOption('controller');

      try {
         $this->generateModel($modelName);
         $io->success("Modèle '{$modelName}' généré avec succès.");

         if ($controllerOption) {
            $this->generateController($modelName);
            $io->success("Contrôleur '{$modelName}Controller' généré avec succès.");
         }

         if ($migrationOption) {
            $this->generateMigration($modelName);
            $io->success("Migration pour '{$modelName}' générée avec succès.");
         }

         $this->appendRoutes($modelName);
         $io->success("Routes CRUD pour '{$modelName}' ajoutées avec succès.");

         $logger->log('info', "Scaffold pour '{$modelName}' complété avec succès.");
         return Command::SUCCESS;
      } catch (\Throwable $th) {
         $io->error("Erreur lors de la génération du scaffold : " . $th->getMessage());
         $logger->log('error', "Erreur lors de la génération du scaffold : " . $th->getMessage());
         return Command::FAILURE;
      }
   }

   private function generateModel(string $modelName): void
   {
      $modelTemplate =
         "<?php\n\nnamespace App\Models;\n\nuse Forge\Database\Iron\Model;\n\nclass " . ucfirst($modelName) . " extends Model\n{\n    protected static \$table = '" . $this->getModelTable($modelName) . "';\n\n    // Logique du modèle ici\n}\n";

      $filePath = "app/Models/{$modelName}.php";
      file_put_contents($filePath, $modelTemplate);
   }

   private function generateController(string $modelName): void
   {
      $controllerTemplate = <<<PHP
<?php

namespace App\Controllers;

use Forge\Http\Request;
use Forge\Http\Response;
use App\Models\\{$modelName};

class {$modelName}Controller extends Controller
{
    public function index(Request \$request)
    {
        \$items = {$modelName}::all();
        return \$response->json(\$items);
    }

    public function show(Request \$request, \$id)
    {
        \$item = {$modelName}::find(\$id);

        if (!\$item) {
            return \$response->status(404)->json(['message' => '{$modelName} introuvable.']);
        }

        return \$response->json(\$item);
    }

    public function store(Request \$request)
    {
        \$data = \$request->all();
        \$item = {$modelName}::create(\$data);

        return \$response->json(['message' => '{$modelName} créé avec succès.', 'data' => \$item]);
    }

    public function update(Request \$request, \$id)
    {
        \$item = {$modelName}::find(\$id);

        if (!\$item) {
            return \$response->status(404)->json(['message' => '{$modelName} introuvable.']);
        }

        \$item->update(\$request->all());
        return \$response->json(['message' => '{$modelName} mis à jour avec succès.', 'data' => \$item]);
    }

    public function destroy(Request \$request, \$id)
    {
        \$item = {$modelName}::find(\$id);

        if (!\$item) {
            return \$response->status(404)->json(['message' => '{$modelName} introuvable.']);
        }

        \$item->delete();
        return \$response->json(['message' => '{$modelName} supprimé avec succès.']);
    }
}
PHP;

      $filePath = "app/Controllers/{$modelName}Controller.php";
      file_put_contents($filePath, $controllerTemplate);
   }

   private function generateMigration(string $modelName): void
   {
      $tableName = strtolower($modelName) . "s";

      $migrationTemplate = <<<PHP
      <?php

      use Forge\Database\Migration;
      use Forge\Database\Iron\Anvil;
      use Forge\Database\Iron\Schema;

      return new class extends Migration
      {
          /**
           * Exécute les migrations.
           */
          public function up(): void
          {
              Schema::create('{$tableName}s', function (Anvil \$table) {
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

      $timestamp = date('Y_m_d_His');
      $migrationFile = __DIR__ . "/../../../database/migrations/{$timestamp}_create_" . strtolower($tableName) . "s_table.php";

      file_put_contents($migrationFile, $migrationTemplate);
   }

   private function appendRoutes(string $modelName): void
   {
      $routeDefinition = <<<PHP

// Routes pour {$modelName}
use App\Controllers\\{$modelName}Controller;

Route::get('/{$modelName}', [{$modelName}Controller::class, 'index']);
Route::get('/{$modelName}/{id}', [{$modelName}Controller::class, 'show']);
Route::post('/{$modelName}', [{$modelName}Controller::class, 'store']);
Route::put('/{$modelName}/{id}', [{$modelName}Controller::class, 'update']);
Route::delete('/{$modelName}/{id}', [{$modelName}Controller::class, 'destroy']);

PHP;

      $filePath = 'routes/web.php';

      if (file_exists($filePath)) {
         file_put_contents($filePath, $routeDefinition, FILE_APPEND);
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
