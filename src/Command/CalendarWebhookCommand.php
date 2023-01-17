<?php

declare(strict_types=1);

namespace Bone\Calendar\Command;

use Bone\Calendar\Service\GoogleCalendarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarWebhookCommand extends Command
{
    private GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        parent::__construct('calendar:webhook');
        $this->googleCalendarService = $googleCalendarService;
    }

    protected function configure()
    {
        $this->setDescription('[webhook] Sets up Google Calendar webhook');
        $this->setHelp('Sets a callback URL that will notify the system if the event is updated on Google');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('📅 Google Calendar webhook config');
        $output->writeln('');

        try {
            $this->googleCalendarService->registerWebhook('lonerganwebhook');
            $output->writeln('✔ Webhook registered.');
        } catch (\Google\Service\Exception $e) {
            if ($e->getErrors()[0]['reason'] !== 'channelIdNotUnique') {
                $output->writeln('💀 Error :');
                $output->writeln('');
                $output->writeln($e->getMessage());

                return Command::FAILURE;
            }

            $output->writeln('✔ Webhook is already registered.');
        }

        $output->writeln('');

        return Command::SUCCESS;
    }
}
