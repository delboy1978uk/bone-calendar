<?php

declare(strict_types=1);

namespace Bone\Calendar\Repository;

use Bone\Calendar\Entity\Calendar;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;

class CalendarRepository extends EntityRepository
{
    /**
     * @param int $id
     * @param int|null $lockMode
     * @param int|null $lockVersion
     * @return Calendar
     * @throws \Doctrine\ORM\ORMException
     */
    public function find($id, $lockMode = null, $lockVersion = null): Calendar
    {
        /** @var Calendar $calendar */
        $calendar =  parent::find($id, $lockMode, $lockVersion);

        if (!$calendar) {
            throw new EntityNotFoundException('Calendar not found.', 404);
        }

        return $calendar;
    }

    /**
     * @param Calendar $calendar
     * @return $calendar
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Calendar $calendar): Calendar
    {
        if(!$calendar->getID()) {
            $this->_em->persist($calendar);
        }

        $this->_em->flush($calendar);

        return $calendar;
    }

    /**
     * @param Calendar $calendar
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete(Calendar $calendar): void
    {
        $this->_em->remove($calendar);
        $this->_em->flush($calendar);
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalCalendarCount(): int
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('count(c.id)');
        $query = $qb->getQuery();

        return (int) $query->getSingleScalarResult();
    }
}
