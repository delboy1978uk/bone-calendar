<?php

declare(strict_types=1);

namespace Bone\Calendar\Command;

use Bone\Calendar\Service\GoogleCalendarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarSyncCommand extends Command
{
    private GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        parent::__construct('calendar:sync');
        $this->googleCalendarService = $googleCalendarService;
    }

    protected function configure()
    {
        $this->setDescription('[sync] Performs initial calendar sync');
        $this->setHelp('Fetches calendar data from Google');
//        $this->addArgument('delete', InputArgument::OPTIONAL, 'remve the webhook');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('📅 Google Calendar Sync');
        $output->writeln('');
        $output->writeln('Fetching Calendar Data..');

        try {
            $x = $this->googleCalendarService->getEvents(new \DateTime('-1 year'),  new \DateTime());
            var_dump($x);
            $output->writeln('✔ Calendar synchronised.');
        } catch (Exception $e) {

                $output->writeln('💀 Error :');
                $output->writeln('');
                $output->writeln($e->getMessage());

                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
