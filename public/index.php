<<<<<<< Updated upstream
=======
<?php

define('_ROOTPATH_', __DIR__ . '/..');

spl_autoload_register();

use App\Controller\Controller;

// Détection du contrôleur depuis l'URL 
$controllerName = $_GET['controller'] ?? 'book';
$controllerClass = 'App\\Controller\\' . ucfirst($controllerName) . 'Controller';

// Vérification que la classe existe
if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    $controller->route();
} else {
    // Contrôleur par défaut 
    $controller = new Controller();
    $controller->route();
}
>>>>>>> Stashed changes
