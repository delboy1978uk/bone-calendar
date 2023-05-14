<?php

declare(strict_types=1);

namespace Bone\Calendar\Command;

use Bone\Calendar\Entity\Calendar;
use Bone\Calendar\Service\CalendarService;
use Bone\Calendar\Service\GoogleCalendarService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Exception\ORMException;
use Google\Service\Calendar\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarSyncCommand extends Command
{
    private GoogleCalendarService $googleCalendarService;
    private CalendarService $calendarService;
    private ?Connection $connection = null;
    private OutputInterface $output;
    private array $processedIds = [];
    private int $newDbEvents = 0;

    public function __construct(GoogleCalendarService $googleCalendarService, CalendarService $calendarService)
    {
        parent::__construct('calendar:sync');
        $this->calendarService = $calendarService;
        $this->googleCalendarService = $googleCalendarService;
    }

    protected function configure()
    {
        $this->setDescription('[sync] Performs initial calendar sync');
        $this->setHelp('Fetches calendar data from Google');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $output->writeln('');
        $output->writeln('📅 Google Calendar Sync');

        try {
            $output->writeln('');
            $output->writeln('⬇ Fetching Google Calendar Data for ' . APPLICATION_ENV . '..');
            $output->writeln('');
            $googleEvents = $this->googleCalendarService->getGoogleEvents(new \DateTime('-1 year'),  new \DateTime('+1 year'));
            $token = $googleEvents->getNextSyncToken();
            $output->writeln('    ' . $token ? 'Storing next sync token ' . $token : 'No sync token receieved from Google.');
            $this->storeNextSyncToken($token);

            foreach ($googleEvents as  $event) {
                $this->handleGoogleEvent($event);
            }

            $output->writeln('');
            $output->writeln('✔ Done, next Google sync token stored');
            $output->writeln('');
            $output->writeln('⬆ Fetching DB Calendar Data..');
            $output->writeln('');
            $dbEvents = $this->calendarService->findEventEntities(new \DateTime('-1 year'),  new \DateTime('+1 year'));

            foreach ($dbEvents as  $event) {
                $this->handleDbEvent($event);
            }

            if ($this->newDbEvents === 0) {
                $output->writeln('    👍 No new DB events needing pushed to Google found.');
            }
        } catch (Exception $e) {
            $output->writeln('💀 Error :' . $e->getMessage());

            return Command::FAILURE;
        }

        $output->writeln('');

        return Command::SUCCESS;
    }

    private function storeNextSyncToken(string $syncToken): void
    {
        if (!$syncToken) {
            return;
        }

        $this->googleCalendarService->storeNextSyncToken($syncToken);
    }

    private function handleGoogleEvent(Event $event): void
    {
        $this->output->writeln('Received Google event ' . $event->getSummary());
        $isDataEvent = $event->getExtendedProperties()?->getPrivate() ?? false;

        if ($isDataEvent)  {
            $this->processEvent($event);
        }
    }

    private function handleDbEvent(Calendar $event): void
    {
        if (\in_array($event->getId(), $this->processedIds)) {
            return;;
        }

        $this->newDbEvents ++;
        $this->output->writeln('Received DB event ' . $event->getEvent());
        $isAlreadyOnGoogle = $event->getExtendedProperties() ? true : false;

        if (!$isAlreadyOnGoogle)  {
            $this->output->writeln('    ⬆ Pushing event ' . $event->getEvent() . ' to google..');
            $this->output->writeln('    👍 DB & Google are in sync.');
            $googleEvent = $this->googleCalendarService->createEvent($event);
            $extendedProperties = (array) $googleEvent->toSimpleObject();
            $event->setExtendedProperties($extendedProperties);
            $this->calendarService->saveCalendar($event);
        }
    }

    private function processEvent(Event $event): void
    {
        $em = $this->calendarService->getRepository()->getEntityManager();
        $this->connection = $this->connection ? $this->connection : $em->getConnection();
        $data = $event->getExtendedProperties()->getPrivate();
        $data['dateFormat'] = \DateTimeInterface::ATOM;
        $extendedProps = (array) $event->toSimpleObject();
        $id = (int) $data['id'];

        try {
            $dbEvent = $this->calendarService->getRepository()->find($id);
            $lastUpdated = new \DateTime($dbEvent->getExtendedProperties()['updated'] ?? 'now');
            $googleUpdated = new \DateTime($event->getUpdated());

            if ($lastUpdated < $googleUpdated) {
                $this->output->writeln('    ⬇ Google event is newer, updating..');
                $dbEvent = $this->calendarService->updateFromArray($dbEvent, $data);
                $dbEvent->setExtendedProperties($extendedProps);
            } else if ($lastUpdated == $googleUpdated) {
                $this->output->writeln('    👍 DB & Google are already in sync.');
            } else {
                $this->output->writeln('    ⬆ DB event is newer, updating Google event.');
                $this->googleCalendarService->updateFromArray($event, $dbEvent);
            }

            $em->flush($dbEvent);
            $this->processedIds[] = $id;
        } catch (ORMException $e) {
            $this->output->writeln('    Event not found in the DB, adding..');
            $this->insertEventNotInDb($data, $extendedProps);
        }
    }

    private function insertEventNotInDb(array $data, array $extendedProps): void
    {
        $sql = 'INSERT INTO `Calendar` (id, event, link, owner, startDate, endDate, status, color, extendedProperties) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $params = [
            $data['id'],
            $data['event'],
            $data['link'],
            $data['owner'],
            $data['startDate'],
            $data['endDate'],
            $data['status'] ?? null,
            $data['color'],
            \json_encode($extendedProps),
        ];
        $result = $this->connection->executeStatement($sql, $params);
    }
}
