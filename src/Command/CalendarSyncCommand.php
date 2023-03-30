<?php

declare(strict_types=1);

namespace Bone\Calendar\Command;

use Bone\Calendar\Service\GoogleCalendarService;
use Google\Service\Calendar\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarSyncCommand extends Command
{
    private GoogleCalendarService $googleCalendarService;
    private OutputInterface $output;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        parent::__construct('calendar:sync');
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
        $output->writeln('');
        $output->writeln('Fetching Calendar Data..');

        try {
            $events = $this->googleCalendarService->getGoogleEvents(new \DateTime('-1 year'),  new \DateTime());

            foreach ($events as  $event) {
                $this->handleEvent($event);
            }
        } catch (Exception $e) {

                $output->writeln('💀 Error :');
                $output->writeln('');
                $output->writeln($e->getMessage());

                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function handleEvent(Event $event): void
    {
        $this->output->writeln('Received event ' . $event->getSummary());
        $isAppointment = $event->getExtendedProperties()?->getPrivate() ?? false;

        if ($isAppointment)  {
            $this->output->writeln('    Processing ' . $event->getSummary());
            $this->processEvent($event);
        }
    }

    private function processEvent(Event $event): void
    {

    }
}
