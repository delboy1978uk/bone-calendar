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
//                var_dump($event); exit;
                $results[] = [
                    'title' => $event->getSummary(),
                    'start' => $event->getStart()->getDate(),
                    'end' => $event->getEnd()->dateTime,
                    'url' => $event->getHtmlLink(),
                    'calendarID' => $event->getSummary(),
                    'status' => null,
                    'color' => 'primary',
                ];
            }

            return $results;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function createEvent(CalendarEvent $event)
    {
        $end = new EventDateTime();
        $end->setDateTime($event->getEndDate()->format('Y-m-d\TH:i:s+00:00'));
        $start = new EventDateTime();
        $start->setDateTime($event->getStartDate()->format('Y-m-d\TH:i:s+00:00'));
        $googleEvent = new Event();
        $googleEvent->setEnd($end);
        $googleEvent->setStart($start);
//        $googleEvent->setColorId('#ff0000');
        $googleEvent->setSummary($event->getEvent());
        try {
            $result = $this->googleCalendar->events->insert($this->calendarId, $googleEvent);
        } catch (\Exception $e) {
            $e->getMessage();
        }

        die(var_dump($result));
    }
}
