<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventsRepository::class)]

#[
    ORM\HasLifecycleCallbacks()
]

class Events
{
    use TimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $venue = null;

    #[ORM\Column(length: 1000)]
    private ?string $shortDescription = null;

    #[ORM\Column(length: 10000)]
    private ?string $longDescription = null;

    #[ORM\Column(length: 255)]
    private ?string $organizer = null;

    #[ORM\Column]
    #[Assert\GreaterThan(0)]
    private ?int $totalTickets = null;

    #[ORM\Column]
    #[Assert\LessThanOrEqual(propertyPath: 'totalTickets')]
    #[Assert\GreaterThan(0)]
    private ?int $availableTickets = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeInterface $startSellTime = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThan(propertyPath: 'startSellTime')]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column]
    #[Assert\GreaterThan(0)]
    private ?int $ticketPrice = null;

    #[ORM\Column(length: 255)]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categories $category = null;

    /**
     * @var Collection<int, EventReservation>
     */
    #[ORM\OneToMany(targetEntity: EventReservation::class, mappedBy: 'event')]
    private Collection $eventReservations;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'event')]
    private Collection $tickets;

    public function __construct()
    {
        $this->eventReservations = new ArrayCollection();
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getVenue(): ?string
    {
        return $this->venue;
    }

    public function setVenue(string $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): static
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function getOrganizer(): ?string
    {
        return $this->organizer;
    }

    public function setOrganizer(string $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getTotalTickets(): ?int
    {
        return $this->totalTickets;
    }

    public function setTotalTickets(int $totalTickets): static
    {
        $this->totalTickets = $totalTickets;

        return $this;
    }

    public function getAvailableTickets(): ?int
    {
        return $this->availableTickets;
    }

    public function setAvailableTickets(int $availableTickets): static
    {
        $this->availableTickets = $availableTickets;

        return $this;
    }

    public function getStartSellTime(): ?\DateTimeInterface
    {
        return $this->startSellTime;
    }

    public function setStartSellTime(\DateTimeInterface $startSellTime): static
    {
        $this->startSellTime = $startSellTime;

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(\DateTimeInterface $eventDate): static
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getTicketPrice(): ?int
    {
        return $this->ticketPrice;
    }

    public function setTicketPrice(int $ticketPrice): static
    {
        $this->ticketPrice = $ticketPrice;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getCategory(): ?Categories
    {
        return $this->category;
    }

    public function setCategory(?Categories $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, EventReservation>
     */
    public function getEventReservations(): Collection
    {
        return $this->eventReservations;
    }

    public function addEventReservation(EventReservation $eventReservation): static
    {
        if (!$this->eventReservations->contains($eventReservation)) {
            $this->eventReservations->add($eventReservation);
            $eventReservation->setEvent($this);
        }

        return $this;
    }

    public function removeEventReservation(EventReservation $eventReservation): static
    {
        if ($this->eventReservations->removeElement($eventReservation)) {
            // set the owning side to null (unless already changed)
            if ($eventReservation->getEvent() === $this) {
                $eventReservation->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setEvent($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getEvent() === $this) {
                $ticket->setEvent(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}


