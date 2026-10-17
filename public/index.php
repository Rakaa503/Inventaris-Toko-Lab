<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/core/Env.php';
require_once ROOT_PATH . '/config/bootstrap.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/App.php';
require_once ROOT_PATH . '/app/controllers/DashboardController.php';

$app = new App();

require_once ROOT_PATH . '/routes/web.php';

$app->run();