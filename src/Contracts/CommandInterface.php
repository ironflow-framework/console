<?php

namespace Forge\CLI\Commands;

interface CommandInterface
{
    public function handle(): void;  // Méthode pour exécuter la commande
    public function getSignature(): string;  // La signature de la commande
}
