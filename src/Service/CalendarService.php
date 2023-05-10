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
        isset($data['id']) ? $calendar->setId((int) $data['id']) : null;
        isset($data['event']) ? $calendar->setEvent($data['event']) : $calendar->setEvent('');
        isset($data['link']) ? $calendar->setLink($data['link']) : $calendar->setLink(null);
        isset($data['owner']) ? $calendar->setOwner((int) $data['owner']) : null;
        isset($data['status']) ? $calendar->setStatus((int) $data['status']) : null;
        isset($data['color']) ? $calendar->setColor($data['color']) : null;
        isset($data['extendedProperties']) ? $calendar->setExtendedProperties($data['extendedProperties']) : '';
        $dateFormat = $data['dateFormat'] ?: 'd/m/Y H:i';

        if (isset($data['startDate'])) {
            $startDate = $data['startDate'] instanceof DateTime ? $data['startDate'] : DateTime::createFromFormat($dateFormat, $data['startDate']);
            $startDate = $startDate ?: null;
            $calendar->setStartDate($startDate);
        }

        if (isset($data['endDate'])) {
            $endDate = $data['endDate'] instanceof DateTime ? $data['endDate'] : DateTime::createFromFormat($dateFormat, $data['endDate']);
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

        throw new Exception('Time slot is not available', 403);
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
        $query->where('e.' . $dateToCkeck . ' > :start');
        $query->andWhere('e.' . $dateToCkeck . ' < :finish');
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

    public function findEventEntities(DateTime $start, DateTime $end)
    {
        $repo = $this->getRepository();
        $query = $repo->createQueryBuilder('qb');
        $query->select('e');
        $query->from(Calendar::class, 'e');
        $query->where('e.startDate BETWEEN :start and :finish');
        $query->setParameter('start', $start);
        $query->setParameter('finish', $end);

        return $query->getQuery()->getResult();
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @return array
     */
    public function findEvents(DateTime $start, DateTime $end): array
    {
        $results =  $this->findEventEntities($start, $end);
        $data = [];

        /** @var Calendar $event */
        foreach ($results as $event) {
            $data[] = [
                'title' => $event->getEvent(),
                'start' => $event->getStartDate()->format(DateTime::ISO8601),
                'end' => $event->getEndDate()->format(DateTime::ISO8601),
                'url' => $event->getLink(),
                'calendarID' => $event->getId(),
                'owner' => $event->getOwner(),
                'status' => $event->getStatus(),
                'color' => $event->getColor(),
            ];
        }

        return $data;
    }

    /**
     * @param int $id
     * @param string $order
     * @return array
     */
    public function getEventsByOwner(int $id, string $order = 'ASC'): array
    {
        return $this->getRepository(Calendar::class)->findBy(['owner' => $id], ['startDate' => $order]);
    }
}
