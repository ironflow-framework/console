<?php

namespace Forge\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use PDO;

class ExportDatabaseCommand extends Command
{
   protected static $defaultName = 'db:export';

   protected function configure(): void
   {
      $this
         ->setName(self::$defaultName)
         ->setDescription('Exporte les données de la base de données.')
         ->addOption('table', null, InputOption::VALUE_REQUIRED, 'Nom de la table à exporter')
         ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Format d\'export (csv, json)', 'csv');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      $table = $input->getOption('table');
      $format = $input->getOption('format');

      // Logique d'exportation des données de la table spécifiée
      $io->text("Exportation des données de la table '$table' au format '$format'...");

      $this->exportData($table, $format);

      $io->success("Données exportées avec succès.");

      return Command::SUCCESS;
   }

   private function exportData($table, $format)
   {
      // Connexion à la base de données
      $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $stmt = $pdo->query("SELECT * FROM $table");
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Exportation au format choisi
      if ($format == 'csv') {
         $this->exportCsv($rows);
      } elseif ($format == 'json') {
         $this->exportJson($rows);
      }
   }

   private function exportCsv($rows)
   {
      $file = fopen('export.csv', 'w');
      fputcsv($file, array_keys($rows[0]));

      foreach ($rows as $row) {
         fputcsv($file, $row);
      }

      fclose($file);
   }

   private function exportJson($rows)
   {
      file_put_contents('export.json', json_encode($rows, JSON_PRETTY_PRINT));
   }
}
