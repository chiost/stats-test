<?php

require 'vendor/autoload.php';

use App\Statistic\Repository\RedisStatisticRepository;
use App\Statistic\Repository\StatisticRepositoryInterface;
use App\Statistic\StatisticService;
use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;
use Slim\Factory\AppFactory;
use Psr\Container\ContainerInterface;
use App\Statistic\Http\Controller\StatisticController;

$container = new class extends Container implements ContainerInterface {
    public function __construct()
    {
        parent::__construct();

        $this->set('redis', function () {
            return new Client();
        });

        $this->set('logger', function () {
            $logger = new Logger('logger');
            $file_handler = new StreamHandler("app.log");
            $logger->pushHandler($file_handler);
            return $logger;
        });

        $this->set(StatisticRepositoryInterface::class, function ($container) {
            return new RedisStatisticRepository($container->get('redis'));
        });

        $this->set(StatisticService::class, function ($container) {
            return new StatisticService($container->get(StatisticRepositoryInterface::class));
        });

        $this->set(StatisticController::class, function ($container) {
            return new StatisticController(
                $container->get(StatisticService::class),
                $container->get('logger')
            );
        });
    }
};

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->post('/statistic/{country}', [$container->get(StatisticController::class), 'updateCountry']);
$app->get('/statistic', [$container->get(StatisticController::class), 'getStatistic']);

$app->run();
