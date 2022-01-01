<?php

declare(strict_types=1);

namespace Bone\Calendar\Form;

use Del\Form\AbstractForm;
use Del\Form\Field\Submit;
use Del\Form\Field\Text;
use Del\Form\Renderer\HorizontalFormRenderer;

class CalendarForm extends AbstractForm
{
    public function init(): void
    {
        $event = new Text('event');
        $event->setLabel('Event');
        $event->setRequired(true);
        $this->addField($event);

        $link = new Text('link');
        $link->setLabel('Link');
        $this->addField($link);

        $owner = new Text('owner');
        $owner->setLabel('Owner');
        $this->addField($owner);

        $startDate = new Text('startDate');
        $startDate->setClass('form-control datetimepicker');
        $startDate->setLabel('Start Date');
        $startDate->setRequired(true);
        $this->addField($startDate);

        $endDate = new Text('endDate');
        $endDate->setClass('form-control datetimepicker');
        $endDate->setLabel('End Date');
        $endDate->setRequired(true);
        $this->addField($endDate);

        $color = new Text('color');
        $color->setLabel('Color');
        $this->addField($color);

        $submit = new Submit('submit');
        $submit->setClass('btn btn-primary pull-right');
        $this->addField($submit);
        $this->setFormRenderer(new HorizontalFormRenderer());
    }
}
