<?php

namespace Forge\CLI;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    protected MonologLogger $logger;

    public function __construct()
    {
        $this->logger = new MonologLogger('forge');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/logs/forge.log', MonologLogger::DEBUG));
    }

    public function log(string $level, string $message): void
    {

        // Log selon le niveau fourni
        switch (strtolower($level)) {
            case 'info':
                $this->logger->info($message);

                break;
            case 'warning':
                $this->logger->warning($message);
                break;
            case 'error':
                $this->logger->error($message);
                break;
            case 'success':
                // Pour success, utiliser info mais en ajoutant un préfixe ou un format spécifique
                $this->logger->info("[SUCCESS] " . $message);
                break;
            default:
                $this->logger->debug($message);
        }
    }

    // Fonction pour appliquer le format de texte en gras
    public function bold(string $message): string
    {
        
        return "\033[1m" . $message . "\033[0m";

        // Si vous souhaitez un format Markdown pour une sortie dans un fichier ou une interface web :
        // return "**" . $message . "**"; // Graser en Markdown
    }
}
