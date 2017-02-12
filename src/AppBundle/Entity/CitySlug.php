<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="city_slug")
 * @JMS\ExclusionPolicy("all")
 */
class CitySlug
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $slug;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="slugs")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): CitySlug
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): CitySlug
    {
        $this->city = $city;

        return $this;
    }
}
