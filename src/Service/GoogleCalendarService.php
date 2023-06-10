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
use Google\Service\Calendar\Events;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendarService
{
    private string $calendarId = '';
    private string $callbackUrl = '';
    private Calendar $googleCalendar;
    private string $syncTokenJsonPath = '';
    private string $channelId = 'webhook';

    public function __construct(string $calendarId, string $callbackUrl, string $syncTokenJsonPath, string $channelId)
    {
        $this->calendarId = $calendarId;
        $this->callbackUrl = $callbackUrl;
        $this->channelId = $channelId;
        $this->syncTokenJsonPath = $syncTokenJsonPath;
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Calendar::CALENDAR);
        $client->addScope(Calendar::CALENDAR_EVENTS);
        $this->googleCalendar = new Calendar($client);
    }

    public function getGoogleEvents(DateTime $start, DateTime $end): Events
    {
        return $this->googleCalendar->events->listEvents($this->calendarId, [
            'timeMin' => $start->format('Y-m-d\TH:i:s\Z'),
            'timeMax' => $end->format('Y-m-d\TH:i:s\Z'),
        ]);
    }

    public function getEventsSinceLastSync(): Events
    {
        $path = \getcwd() . '/' . $this->syncTokenJsonPath;
        $json = \file_get_contents($path);
        $data = \json_decode($json, true);
        $syncToken = $data['sync_token'];
        $events = $this->googleCalendar->events->listEvents($this->calendarId, [
            'syncToken' => $syncToken
        ]);
        $syncToken = $data['sync_token'] = $events->getNextSyncToken();
        $json = \json_encode($data);
        \file_put_contents($path, $json);

        return $events;
    }

    public function getEvents(DateTime $start, DateTime $end): array
    {
        try {
            $events = $this->getGoogleEvents($start, $end);
            $results = [];

            /** @var Event $event */
            foreach ($events as $event) {
                if ($properties = $event->getExtendedProperties()?->getPrivate()) {
                    $results[] = [
                        'title' => $properties['event'],
                        'start' => $event->getStart()->getDateTime(),
                        'end' => $event->getEnd()->getDateTime(),
                        'url' => $properties['link'],
                        'calendarID' => $properties['id'],
                        'status' => $properties['status'] ?? null,
                        'color' => $properties['color'] ?? null,
                    ];
                } else {
                    $data = [
                        'title' => $event->getSummary(),
                        'url' => $event->getHtmlLink(),
                        'color' => $event->getColorId(),
                        'calendarID' => $event->getId(),
                    ];

                    $start = $event->getStart()->getDateTime();
                    $end = $event->getEnd()->getDateTime();

                    if (null !== $start && null !== $end) {
                        $data['start'] = $start;
                        $data['end'] = $end;
                    } else {
                        $data['allDay'] = true;
                        $data['start'] = $event->getStart()->getDate();
                        $data['end'] = $event->getEnd()->getDate();
                    }

                    $results[] = $data;
                }
            }

            return $results;
        } catch (Exception $e) {
            throw $e;
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

    public function registerWebhook(): Calendar\Channel
    {
        $channel = new Calendar\Channel();
        $channel->setId($this->channelId);
        $channel->setType('webhook');
        $channel->setAddress($this->callbackUrl);
        $result = $this->googleCalendar->events->watch($this->calendarId, $channel);
        $path = \getcwd() . '/' . $this->syncTokenJsonPath;
        $json = \file_get_contents($path);
        $data = \json_decode($json, true);
        $data['resourceId'] = $result->getResourceId();
        $json = \json_encode($data);
        \file_put_contents($path, $json);

        return $result;
    }

    public function removeWebhook()
    {
        $path = \getcwd() . '/' . $this->syncTokenJsonPath;
        $json = \file_get_contents($path);
        $data = \json_decode($json, true);
        $channel = new Calendar\Channel();
        $channel->setId($this->channelId);
        $channel->setResourceId($data['resourceId']);
        $channel->setType('webhook');
        $channel->setAddress($this->callbackUrl);

        return $this->googleCalendar->channels->stop($channel);
    }

    public function getHooks(): Calendar\Settings
    {
        return $this->googleCalendar->settings->listSettings();
    }

    public function deleteEvent(string $eventId): void
    {
        $this->googleCalendar->events->delete($this->calendarId, $eventId);
    }

    public function storeNextSyncToken(string $syncToken): void
    {
        $path = \getcwd() . '/' . $this->syncTokenJsonPath;

        if (\file_exists($path)) {
            $json = \file_get_contents($path);
            $data = \json_decode($json, true);
        }

        $data['sync_token'] = $syncToken;
        $json = \json_encode($data);
        \file_put_contents($path, $json);
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

    /**
     * @return string
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }
}
