<?php

declare(strict_types=1);

namespace Bone\Calendar\Service;

use Bone\Calendar\Entity\Calendar;
use Bone\Calendar\Repository\CalendarRepository;
use Bone\Exception;
use DateTime;
use Doctrine\ORM\EntityManager;

class CalendarService
{
    /** @var EntityManager $em */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param array $data
     * @return Calendar
     */
    public function createFromArray(array $data): Calendar
    {
        $calendar = new Calendar();

        return $this->updateFromArray($calendar, $data);
    }

    /**
     * @param Calendar $calendar
     * @param array $data
     * @return Calendar
     */
    public function updateFromArray(Calendar $calendar, array $data): Calendar
    {
        isset($data['id']) ? $calendar->setId($data['id']) : null;
        isset($data['event']) ? $calendar->setEvent($data['event']) : $calendar->setEvent('');
        isset($data['link']) ? $calendar->setLink($data['link']) : $calendar->setLink(null);
        isset($data['owner']) ? $calendar->setOwner((int) $data['owner']) : null;

        if (isset($data['startDate'])) {
            $startDate = $data['startDate'] instanceof DateTime ? $data['startDate'] : DateTime::createFromFormat('d/m/Y H:i', $data['startDate']);
            $startDate = $startDate ?: null;
            $calendar->setStartDate($startDate);
        }

        if (isset($data['endDate'])) {
            $endDate = $data['endDate'] instanceof DateTime ? $data['endDate'] : DateTime::createFromFormat('d/m/Y H:i', $data['endDate']);
            $endDate = $endDate ?: null;
            $calendar->setEndDate($endDate);
        }

        return $calendar;
    }

    /**
     * @param Calendar $calendar
     * @return Calendar
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveCalendar(Calendar $calendar): Calendar
    {

        if ($this->checkTimeSlotIsFree($calendar) && $this->checkTimeSlotIsFree($calendar, 'endDate')) {
            return $this->getRepository()->save($calendar);
        }

        throw new Exception(' Time slot is not available');
    }

    /**
     * @param Calendar $calendar
     * @param string $dateToCkeck
     * @return bool
     */
    public function checkTimeSlotIsFree(Calendar $calendar, string $dateToCkeck = 'startDate'): bool
    {
        $repo = $this->getRepository();
        $query = $repo->createQueryBuilder('qb');
        $query->select('e');
        $query->from(Calendar::class, 'e');
        $query->where('e.' . $dateToCkeck . ' BETWEEN :start and :finish');
        $query->setParameter('start', $calendar->getStartDate());
        $query->setParameter('finish', $calendar->getEndDate());

        if ($calendar->getId()) {
            $query->andWhere('e.id != :id');
            $query->setParameter('id', $calendar->getId());
        }

        $results = $query->getQuery()->getResult();

        if (count($results)) {
            return false;
        }

        return true;
    }

    /**
     * @param Calendar $calendar
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteCalendar(Calendar $calendar): void
    {
        $this->getRepository()->delete($calendar);
    }

    /**
     * @return CalendarRepository
     */
    public function getRepository(): CalendarRepository
    {
        /** @var CalendarRepository $repository */
        $repository = $this->em->getRepository(Calendar::class);

        return $repository;
    }
}
