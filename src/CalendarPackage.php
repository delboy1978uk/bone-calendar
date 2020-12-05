<?php

declare(strict_types=1);

namespace Bone\Calendar;

use Barnacle\Container;
use Barnacle\EntityRegistrationInterface;
use Barnacle\RegistrationInterface;
use Bone\Calendar\Controller\CalendarApiController;
use Bone\Calendar\Controller\CalendarController;
use Bone\Calendar\Service\CalendarService;
use Bone\Controller\Init;
use Bone\Http\Middleware\HalCollection;
use Bone\Http\Middleware\HalEntity;
use Bone\Router\Router;
use Bone\Router\RouterConfigInterface;
use Bone\User\Http\Middleware\SessionAuth;
use Bone\View\ViewRegistrationInterface;
use Del\Booty\AssetRegistrationInterface;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\ResponseFactory;
use League\Route\RouteGroup;
use League\Route\Strategy\JsonStrategy;

class CalendarPackage implements RegistrationInterface, RouterConfigInterface, EntityRegistrationInterface, ViewRegistrationInterface, AssetRegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {
        $c[CalendarService::class] = $c->factory(function (Container $c) {
            $em =  $c->get(EntityManager::class);

            return new CalendarService($em);
        });

        $c[CalendarController::class] = $c->factory(function (Container $c) {
            $service = $c->get(CalendarService::class);

            return Init::controller(new CalendarController($service), $c);
        });

        $c[CalendarApiController::class] = $c->factory(function (Container $c) {
            $service = $c->get(CalendarService::class);

            return new CalendarApiController($service);
        });
    }

    /**
     * @return array
     */
    public function addViews(): array
    {
        return ['calendar' => __DIR__ . '/View/Calendar'];
    }

    /**
     * @param Container $c
     * @return array
     */
    public function addViewExtensions(Container $c): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getEntityPath(): string
    {
        return __DIR__ . '/Entity';
    }

    /**
     * @return string[]
     */
    public function getAssetFolders(): array
    {
        return [
            'bone-calendar' => dirname(__DIR__) . '/data/assets',
        ];
    }


    /**
     * @param Container $c
     * @param Router $router
     * @return Router
     */
    public function addRoutes(Container $c, Router $router): Router
    {
        $auth = $c->get(SessionAuth::class);
        $router->group('/admin/calendar', function (RouteGroup $route) {
            $route->map('GET', '/', [CalendarController::class, 'index']);
            $route->map('GET', '/{id:number}', [CalendarController::class, 'view']);
            $route->map('GET', '/create', [CalendarController::class, 'create']);
            $route->map('GET', '/edit/{id:number}', [CalendarController::class, 'edit']);
            $route->map('GET', '/delete/{id:number}', [CalendarController::class, 'delete']);
            $route->map('GET', '/view', [CalendarController::class, 'calendarView']);

            $route->map('POST', '/create', [CalendarController::class, 'create']);
            $route->map('POST', '/edit/{id:number}', [CalendarController::class, 'edit']);
            $route->map('POST', '/delete/{id:number}', [CalendarController::class, 'delete']);
        })->middlewares([$auth]);

        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);

        $router->group('/api/calendar', function (RouteGroup $route) {
            $route->map('GET', '/', [CalendarApiController::class, 'index'])->prependMiddleware(new HalCollection(5));
            $route->map('POST', '/', [CalendarApiController::class, 'create']);
            $route->map('GET', '/{id:number}', [CalendarApiController::class, 'view'])->prependMiddleware(new HalEntity());
            $route->map('PUT', '/{id:number}', [CalendarApiController::class, 'update']);
            $route->map('DELETE', '/{id:number}', [CalendarApiController::class, 'delete']);
        })
        ->setStrategy($strategy);

        return $router;
    }
}
