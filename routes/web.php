<?php

declare(strict_types=1);

$app->get('/', static function (): void {
    (new DashboardController())->index();
});