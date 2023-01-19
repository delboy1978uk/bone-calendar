<?php

declare(strict_types=1);

namespace Bone\Calendar\Command;

use Bone\Calendar\Service\GoogleCalendarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addArgument('delete', InputArgument::OPTIONAL, 'remve the webhook');
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

        if ($input->hasArgument('delete')) {
            $output->writeln('Deleting webhook..');

            return $this->remove($output);
        } else {
            $output->writeln('Registering webhook..');

            return $this->register($output);
        }
    }

    private function register(OutputInterface $output): int
    {
        try {
            $x = $this->googleCalendarService->registerWebhook('xxxxx');
            var_dump($x);
            $output->writeln('✔ Webhook registered.');
        } catch (\Google\Service\Exception $e) {
            if ($e->getErrors()[0]['reason'] !== 'channelIdNotUnique') {
                $output->writeln('💀 Error :');
                $output->writeln('');
                $output->writeln($e->getMessage());

                return Command::FAILURE;
            }

            $output->writeln('✔ Webhook is already registered.');
            $output->writeln('');

            return Command::SUCCESS;
        }
    }

    private function remove(OutputInterface $output): int
    {
        try {
            $this->googleCalendarService->removeWebhook('xxxxx');
            $output->writeln('✔ Webhook removed.');
        } catch (\Google\Service\Exception $e) {
                $output->writeln('💀 Error :');
                $output->writeln('');
                $output->writeln($e->getMessage());

                return Command::FAILURE;
        }

        $output->writeln('');

        return Command::SUCCESS;
    }
}
