<?php

declare(strict_types=1);

namespace Bone\Calendar\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarWebhookCommand extends Command
{
    public function __construct()
    {
        parent::__construct('calendar:webhook');
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
        $output->writeln('📅 Google Calendar webhook config');

        return Command::SUCCESS;
    }
}
