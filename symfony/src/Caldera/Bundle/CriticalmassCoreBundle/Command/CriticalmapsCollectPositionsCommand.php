<?php

namespace Caldera\Bundle\CriticalmassCoreBundle\Command;

use Caldera\Bundle\CalderaBundle\Entity\City;
use Caldera\Bundle\CalderaBundle\Entity\CitySlug;
use Caldera\Bundle\CalderaBundle\Entity\Position;
use Caldera\Bundle\CalderaBundle\Entity\Ride;
use Caldera\Bundle\CalderaBundle\Entity\Ticket;
use Caldera\Bundle\CriticalmassCoreBundle\Glympse\Exception\GlympseApiBrokenException;
use Caldera\Bundle\CriticalmassCoreBundle\Glympse\Exception\GlympseApiErrorException;
use Caldera\Bundle\CriticalmassCoreBundle\Glympse\Exception\GlympseException;
use Caldera\Bundle\CriticalmassCoreBundle\Glympse\Exception\GlympseInviteUnknownException;
use Caldera\Bundle\CriticalmassCoreBundle\Statistic\RideEstimate\RideEstimateService;
use Curl\Curl;
use Doctrine\ORM\EntityManager;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CriticalmapsCollectPositionsCommand extends ContainerAwareCommand
{
    /** @var InputInterface $input */
    protected $input;

    /** @var OutputInterface $output */
    protected $output;

    /** @var EntityManager $manager */
    protected $manager;

    /** @var string $accessToken */
    protected $accessToken;

    protected function configure()
    {
        $this
            ->setName('criticalmass:criticalmaps:collect-positions')
            ->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->manager = $this->getContainer()->get('doctrine')->getManager();

        $locations = $this->fetchLocations();

        $this->savePositions($locations);

        $this->manager->flush();
    }

    protected function fetchLocations()
    {
        $curl = new Curl();
        $curl->get('http://api.criticalmaps.net/get');

        $result = json_decode($curl->response);

        $locations = $result->locations;

        return $locations;
    }

    protected function savePositions($locations)
    {
        foreach ($locations as $location) {
            $position = $this->convertLocationToPosition($location);

            $ride = $this->findRideForPosition($position);

            echo $ride->getCity()->getCity();
        }
    }

    protected function convertLocationToPosition($location): Position
    {
        $latitude = $location->latitude / 1000000;
        $longitude = $location->longitude / 1000000;
        $timestamp = $location->timestamp;
        $dateTime = new \DateTime();

        $position = new Position();
        $position
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setTimestamp($timestamp)
            ->setCreationDateTime($dateTime);

        return $position;
    }

    public function findRideForPosition(Position $position): Ride
    {
        $finder = $this->getContainer()->get('fos_elastica.finder.criticalmass.ride');

        $geoFilter = new \Elastica\Filter\GeoDistance(
            'pin',
            [
                'lat' => $position->getLatitude(),
                'lon' => $position->getLongitude()
            ],
            '30km'
        );

        $filteredQuery = new \Elastica\Query\Filtered(new \Elastica\Query\MatchAll(), $geoFilter);

        $query = new \Elastica\Query($filteredQuery);

        $query->setSize(1);
        $query->setSort(
            [
                '_geo_distance' =>
                    [
                        'pin' =>
                            [
                                $position->getLatitude(),
                                $position->getLongitude()
                            ],
                        'order' => 'asc',
                        'unit' => 'km'
                    ]
            ]
        );

        $results = $finder->find($query);

        return array_pop($results);
    }
}