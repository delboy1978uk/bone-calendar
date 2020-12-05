<?php

declare(strict_types=1);

namespace Bone\Calendar\Collection;

use Bone\Calendar\Entity\Calendar;
use Doctrine\Common\Collections\ArrayCollection;
use JsonSerializable;
use LogicException;

class CalendarCollection extends ArrayCollection implements JsonSerializable
{
    /**
     * @param Calendar $calendar
     * @return $this
     * @throws LogicException
     */
    public function update(Calendar $calendar): CalendarCollection
    {
        $key = $this->findKey($calendar);

        if($key) {
            $this->offsetSet($key,$calendar);

            return $this;
        }

        throw new LogicException('Calendar was not in the collection.');
    }

    /**
     * @param Calendar $calendar
     */
    public function append(Calendar $calendar): void
    {
        $this->add($calendar);
    }

    /**
     * @return Calendar|null
     */
    public function current(): ?Calendar
    {
        return parent::current();
    }

    /**
     * @param Calendar $calendar
     * @return int|null
     */
    public function findKey(Calendar $calendar): ?int
    {
        $it = $this->getIterator();
        $it->rewind();

        while($it->valid()) {

            if($it->current()->getId() == $calendar->getId()) {
                return $it->key();
            }

            $it->next();
        }

        return null;
    }

    /**
     * @param int $id
     * @return Calendar|null
     */
    public function findById(int $id): ?Calendar
    {
        $it = $this->getIterator();
        $it->rewind();

        while($it->valid()) {

            if($it->current()->getId() == $id) {
                return $it->current();
            }

            $it->next();
        }

        return null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $collection = [];
        $it = $this->getIterator();
        $it->rewind();

        while($it->valid()) {
            /** @var Calendar $row */
            $row = $it->current();
            $collection[] = $row->toArray();
            $it->next();
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function jsonSerialize(): string
    {
        return \json_encode($this->toArray());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->jsonSerialize();
    }
}
