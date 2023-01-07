<?php

declare(strict_types=1);

namespace Bone\Calendar\Service;

use Bone\Calendar\Entity\Calendar as CalendarEvent;
use DateTime;
use DateTimeInterface;
use Exception;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendarService
{
    private string $calendarId = '';
    private Calendar $googleCalendar;

    public function __construct(string $calendarId)
    {
        $this->calendarId = $calendarId;
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Calendar::CALENDAR);
        $client->addScope(Calendar::CALENDAR_EVENTS);
        $this->googleCalendar = new Calendar($client);
    }

    public function getEvents(DateTime $start, DateTime $end): array
    {
        try {
            $events = $this->googleCalendar->events->listEvents($this->calendarId, [
                'timeMin' => $start->format('Y-m-d\TH:i:s\Z'),
                'timeMax' => $end->format('Y-m-d\TH:i:s\Z'),
            ]);
            $results = [];

            /** @var Event $event*/
            foreach ($events as $event) {
                $properties = $event->getExtendedProperties()->getPrivate();
                $results[] = [
                    'title' => $properties['event'],
                    'start' => $properties['startDate'],
                    'end' => $properties['endDate'],
                    'url' => $properties['link'],
                    'calendarID' => $properties['id'],
                    'status' => $properties['status'] ?? null,
                    'color' => $properties['color'] ?? null,
                ];

            }

            return $results;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function createEvent(CalendarEvent $event): Event
    {
        $data = $event->toArray('Y-m-d\TH:i:s+00:00');
        $properties = new Calendar\EventExtendedProperties();
        $properties->setPrivate($data);
        $end = new EventDateTime();
        $end->setDateTime($event->getEndDate()->format('Y-m-d\TH:i:s+00:00'));
        $start = new EventDateTime();
        $start->setDateTime($event->getStartDate()->format('Y-m-d\TH:i:s+00:00'));
        $googleEvent = new Event();
        $googleEvent->setEnd($end);
        $googleEvent->setStart($start);
        $colorId = $this->getGoogleColorId($event);
        $googleEvent->setColorId($colorId);
        $googleEvent->setSummary($event->getEvent());
        $googleEvent->setExtendedProperties($properties);

        return $this->googleCalendar->events->insert($this->calendarId, $googleEvent);
    }

    public function getEvent($id): Event
    {
        return $this->googleCalendar->events->get($this->calendarId, $id);
    }

    public function updateFromArray(Event $googleEvent, CalendarEvent $event): Event
    {
        $googleEvent->setSummary($event->getEvent());
        $end = new EventDateTime();
        $end->setDateTime($event->getEndDate()->format('Y-m-d\TH:i:s+00:00'));
        $start = new EventDateTime();
        $start->setDateTime($event->getStartDate()->format('Y-m-d\TH:i:s+00:00'));
        $googleEvent->setEnd($end);
        $googleEvent->setStart($start);
        $data = $event->toArray('Y-m-d\TH:i:s+00:00');
        $properties = new Calendar\EventExtendedProperties();
        $properties->setPrivate($data);
        $colorId = $this->getGoogleColorId($event);
        $googleEvent->setColorId($colorId);
        $googleEvent->setExtendedProperties($properties);

        return $this->googleCalendar->events->update($this->calendarId, $googleEvent->getId(), $googleEvent);
    }

    private function getGoogleColorId(CalendarEvent $event): int
    {
        switch ($event->getColor()) {
            case 'teal':
                return 2;
            case 'indigo':
                return 3;
            case 'red':
                return 4;
            case 'orange':
                return 5;
            case 'info':
                return 7;
            case 'primary':
            default:
                return 1;
        }
    }
}
