<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TimeTrait
{

    #[ORM\Column(length: 255, type: "datetime", nullable: true)]

    private ?\DateTime $createdAt;

    #[ORM\PrePersist()]
    public function onCreate(): void
    {
        $this->createdAt = $this->createdAt ?? new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate()]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    #[ORM\Column(length: 255, type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
