<?php

declare(strict_types=1);

namespace Bone\Calendar\Controller;

use Bone\Calendar\Collection\CalendarCollection;
use Bone\Calendar\Form\CalendarForm;
use Bone\Calendar\Service\CalendarService;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CalendarApiController
{
    /** @param CalendarService $service */
    private $service;

    /**
     * @param CalendarService $service
     */
    public function __construct(CalendarService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $limit = $params['limit'];
        $offset = $params['offset'];
        $db = $this->service->getRepository();
        $calendars = new CalendarCollection($db->findBy([], null, $limit, $offset));
        $total = $db->getTotalCalendarCount();
        $count = count($calendars);
        if ($count < 1) {
            throw new NotFoundException();
        }

        $payload['_embedded'] = $calendars->toArray();
        $payload['count'] = $count;
        $payload['total'] = $total;

        return new JsonResponse($payload);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $post = json_decode($request->getBody()->getContents(), true) ?: $request->getParsedBody();
        $form = new CalendarForm('create');
        $form->populate($post);

        if ($form->isValid()) {
            $data = $form->getValues();
            $calendar = $this->service->createFromArray($data);
            $this->service->saveCalendar($calendar);

            return new JsonResponse($calendar->toArray());
        }

        return new JsonResponse([
            'error' => $form->getErrorMessages(),
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     */
    public function view(ServerRequestInterface $request): ResponseInterface
    {
        $calendar = $this->service->getRepository()->find($request->getAttribute('id'));

        return new JsonResponse($calendar->toArray());
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $db = $this->service->getRepository();
        $calendar = $db->find($request->getAttribute('id'));

        $post = json_decode($request->getBody()->getContents(), true) ?: $request->getParsedBody();
        $form = new CalendarForm('update');
        $form->populate($post);

        if ($form->isValid()) {
            $data = $form->getValues();
            $calendar = $this->service->updateFromArray($calendar, $data);
            $this->service->saveCalendar($calendar);

            return new JsonResponse($calendar->toArray());
        }

        return new JsonResponse([
            'error' => $form->getErrorMessages(),
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $db = $this->service->getRepository();
        $calendar = $db->find($request->getAttribute('id'));
        $this->service->deleteCalendar($calendar);

        return new JsonResponse(['deleted' => true]);
    }
}
