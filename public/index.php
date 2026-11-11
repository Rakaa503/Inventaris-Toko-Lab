<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

require_once ROOT_PATH . '/vendor/autoload.php';

SecurityHeaders::send();

$app = new App();

require_once ROOT_PATH . '/routes/web.php';

$app->run();