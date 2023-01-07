<?php

declare(strict_types=1);

namespace Bone\Calendar\Controller;

use Bone\Calendar\Collection\CalendarCollection;
use Bone\Calendar\Form\CalendarForm;
use Bone\Calendar\Service\CalendarService;
use Bone\Calendar\Service\GoogleCalendarService;
use Bone\Exception;
use DateTime;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CalendarApiController
{
    private CalendarService $service;
    private GoogleCalendarService $googleCalendarService;

    /**
     * @param CalendarService $service
     */
    public function __construct(CalendarService $service, GoogleCalendarService $googleCalendarService = null)
    {
        $this->service = $service;
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function calendarEvents(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $start = $query['start'] ?? null;
        $end = $query['end'] ?? null;

        if (!$start && !$end) {
            throw new Exception('You must supply a start and end date.', 400);
        }

        $events = $this->service->findEvents(new DateTime($start), new DateTime($end));
        $events2 = $this->googleCalendarService->getEvents(new DateTime($start), new DateTime($end));

        return new JsonResponse($events);
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
            $data['dateFormat'] = 'Y-m-d\TH:i:s.v\Z';
            $calendar = $this->service->createFromArray($data);
            $this->service->saveCalendar($calendar);
            $googleEvent = $this->googleCalendarService->createEvent($calendar);
            $calendar->setExtendedProperties((array) $googleEvent->toSimpleObject());
            $this->service->saveCalendar($calendar);

            return new JsonResponse($calendar->toArray($data['dateFormat']));
        }

        return new JsonResponse([
            'error' => $form->getErrorMessages(),
        ], 400);
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
            $data['dateFormat'] = 'Y-m-d\TH:i:s.v\Z';
            $calendar = $this->service->updateFromArray($calendar, $data);
            $this->service->saveCalendar($calendar);
            $googleEvent = $this->googleCalendarService->getEvent($calendar->getExtendedProperties()['id']);
            $this->googleCalendarService->updateFromArray($googleEvent, $calendar);

            return new JsonResponse($calendar->toArray($data['dateFormat']));
        }

        return new JsonResponse([
            'error' => $form->getErrorMessages(),
        ], 400);
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
