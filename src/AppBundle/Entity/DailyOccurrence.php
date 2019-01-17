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
 * An occurrence of an Event.
 *
 * @ORM\Entity
 *
 * @ApiResource(
 *   itemOperations={
 *     "get"={
 *       "method"="GET",
 *       "path"="/calendar/{id}",
 *       "requirements"={"id"="\d+"}
 *     }
 *   },
 *   collectionOperations={
 *     "get"={
 *       "method"="GET",
 *       "path"="/calendar",
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "method"="GET",
 *       "path"="/calendar/{id}",
 *       "requirements"={"id"="\d+"}
 *     }
 *   },
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "occurrence_read" } },
 *     "denormalization_context" = { "groups" = { "event_write" } },
 *     "filters" = { "occurrence.search", "occurrence.search.date", "occurrence.search.event_tag", "occurrence.search.published", "occurrence.order" }
 *   }
 * )
 *
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="IDX_OCCURRENCE_DATES", columns={"start_date", "end_date"})
 *   }
 * )
 */
class DailyOccurrence extends Occurrence
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __construct(Occurrence $occurrence)
    {
        foreach (get_object_vars($occurrence) as $key => $name) {
            $this->$key = $name;
        }

        $this->setOccurrence($occurrence);
    }

    /**
     * @ORM\ManyToOne(targetEntity="Occurrence", inversedBy="dailyOccurrences", fetch="EXTRA_LAZY")
     */
    private $occurrence;

    /**
     * Get occurrence
     *
     * @return Occurrence
     */
    public function getOccurrence(): Occurrence
    {
        return $this->occurrence;
    }

    /**
     * Set occurrence
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
