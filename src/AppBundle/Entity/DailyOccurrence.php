<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * A daily occurrence of an Event.
 *
 * @ORM\Entity
 *
 * @ApiResource(
 *   collectionOperations={"get"},
 *   itemOperations={"get"},
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "occurrence_read" } },
 *     "filters" = { "occurrence.search", "occurrence.search.date", "occurrence.search.event_tag", "occurrence.search.published", "occurrence.search.access", "occurrence.order" }
 *   }
 * )
 *
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="IDX_OCCURRENCE_DATES", columns={"start_date", "end_date"})
 *   }
 * )
 */
class DailyOccurrence
{
    use OccurrenceTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Occurrence")
     * @ORM\JoinColumn(name="occurrence_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $occurrence;

    /**
     * Sets id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get occurrence.
     *
     * @return Occurrence
     */
    public function getOccurrence(): Occurrence
    {
        return $this->occurrence;
    }

    /**
     * Set occurrence.
     *
     * @param $occurrence
     *
     * @return DailyOccurrence
     */
    public function setOccurrence(Occurrence $occurrence): self
    {
        $this->occurrence = $occurrence;

        return $this;
    }
}
