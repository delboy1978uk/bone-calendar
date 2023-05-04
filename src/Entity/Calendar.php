<?php

declare(strict_types=1);

namespace Bone\Calendar\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="\Bone\Calendar\Repository\CalendarRepository")
 */
class Calendar implements JsonSerializable
{
    /**
     * @var int $id
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string $event
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $event;

    /**
     * @var string $link
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $link;

    /**
     * @var int $owner
     * @ORM\Column(type="integer", length=6, nullable=true)
     */
    private $owner;

    /**
     * @var DateTime $startDate
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $startDate;

    /**
     * @var DateTime $endDate
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $endDate;

    /**
     * @var ?int $status
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @var ?string $color
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $color;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $extendedProperties = null;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     */
    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return int
     */
    public function getOwner(): ?int
    {
        return $this->owner;
    }

    /**
     * @param int|null $owner
     */
    public function setOwner(?int $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     */
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     */
    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int|null $status
     */
    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string|null $color
     */
    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getExtendedProperties(): ?array
    {
        return $this->extendedProperties ? \json_decode($this->extendedProperties, true) : null;
    }

    /**
     * @param array $extendedProperties
     */
    public function setExtendedProperties(array $extendedProperties): void
    {
        $this->extendedProperties = \json_encode($extendedProperties);
    }

    /**
     * @return array
     * @param string $dateFormat
     */
    public function toArray(string $dateFormat = 'd/m/Y'): array
    {
        $data = [
            'id' => $this->getId(),
            'event' => $this->getEvent(),
            'link' => $this->getLink(),
            'owner' => $this->getOwner(),
            'startDate' => ($startDate = $this->getStartDate()) ? $startDate->format($dateFormat) : null,
            'endDate' => ($endDate = $this->getEndDate()) ? $endDate->format($dateFormat) : null,
            'status' => $this->getStatus() ? $this->getStatus() : null,
            'color' => $this->getColor(),
        ];

        return $data;
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
