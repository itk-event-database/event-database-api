<?php

namespace AdminBundle\Service;

use Tests\AppBundle\Test\ContainerTestCase;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\ValueConverter;

class FeedReaderTimeZoneTest extends ContainerTestCase implements Controller {
  /**
   * @var ValueConverter
   */
  private $converter;

  /**
   * @var FileHandler
   */
  private $fileHandler;

  private $events = [];


  public function testReadFeed() {
    $feedConfiguration = $this->readFixture('feed.config.yml');

    $utcTimeZone = new \DateTimeZone('UTC');
    foreach (self::$timeZoneNames as $timeZoneName) {
      $this->events = [];
      $feedConfiguration['timeZone'] = $timeZoneName;
      $timeZone = new \DateTimeZone($feedConfiguration['timeZone']);

      $json = $this->readFixture('feed.data.yml');
      $feed = $this->createFeed($feedConfiguration);
      $reader = $this->container->get('feed_reader.json');
      $reader
        ->setController($this)
        ->setFeed($feed);
      $reader->read($json);

      $this->assertEquals(1, count($this->events));
      $event = $this->events[0];
      $this->assertEquals(2, count($event['occurrences']));
      $occurrence = $event['occurrences'][0];
      $this->assertEquals(new \DateTime('2001-01-01T08:00:00', $timeZone), $occurrence['startDate'], $timeZoneName);
      $this->assertEquals(new \DateTime('2001-01-01T12:00:00', $timeZone), $occurrence['endDate'], $timeZoneName);
      $occurrence = $event['occurrences'][1];
      $this->assertEquals(new \DateTime('2001-01-01T08:00:00+01:00'), $occurrence['startDate'], $timeZoneName);
      $this->assertEquals(new \DateTime('2001-01-01T12:00:00+01:00'), $occurrence['endDate'], $timeZoneName);
    }
  }

  private function createFeed(array $configuration) {
    $feed = new Feed();
    $feed->setConfiguration($configuration);
    $this->converter = $this->container->get('value_converter');
    $this->converter->setFeed($feed);
    $this->fileHandler = $this->container->get('file_handler');

    return $feed;
  }

  public function createEvent(array $data) {
    if (isset($data['image'])) {
      $data['original_image'] = $data['image'];
      $data['image'] = $this->fileHandler->download($data['image']);
    }

    $this->events[] = $data;
  }

  public function convertValue($value, $name) {
    return $this->converter->convert($value, $name);
  }

  private static $timeZoneNames = [
    'Africa/Asmera',
    'Africa/Timbuktu',
    'America/Argentina/ComodRivadavia',
    'America/Atka',
    'America/Buenos_Aires',
    'America/Catamarca',
    'America/Coral_Harbour',
    'America/Cordoba',
    'America/Ensenada',
    'America/Fort_Wayne',
    'America/Indianapolis',
    'America/Jujuy',
    'America/Knox_IN',
    'America/Louisville',
    'America/Mendoza',
    'America/Montreal',
    'America/Porto_Acre',
    'America/Rosario',
    'America/Santa_Isabel',
    'America/Shiprock',
    'America/Virgin',
    'Antarctica/South_Pole',
    'Asia/Ashkhabad',
    'Asia/Calcutta',
    'Asia/Chongqing',
    'Asia/Chungking',
    'Asia/Dacca',
    'Asia/Harbin',
    'Asia/Istanbul',
    'Asia/Kashgar',
    'Asia/Katmandu',
    'Asia/Macao',
    'Asia/Rangoon',
    'Asia/Saigon',
    'Asia/Tel_Aviv',
    'Asia/Thimbu',
    'Asia/Ujung_Pandang',
    'Asia/Ulan_Bator',
    'Atlantic/Faeroe',
    'Atlantic/Jan_Mayen',
    'Australia/ACT',
    'Australia/Canberra',
    'Australia/LHI',
    'Australia/North',
    'Australia/NSW',
    'Australia/Queensland',
    'Australia/South',
    'Australia/Tasmania',
    'Australia/Victoria',
    'Australia/West',
    'Australia/Yancowinna',
    'Brazil/Acre',
    'Brazil/DeNoronha',
    'Brazil/East',
    'Brazil/West',
    'Canada/Atlantic',
    'Canada/Central',
    'Canada/East-Saskatchewan',
    'Canada/Eastern',
    'Canada/Mountain',
    'Canada/Newfoundland',
    'Canada/Pacific',
    'Canada/Saskatchewan',
    'Canada/Yukon',
    'CET',
    'Chile/Continental',
    'Chile/EasterIsland',
    'CST6CDT',
    'Cuba',
    'EET',
    'Egypt',
    'Eire',
    'EST',
    'EST5EDT',
    'Etc/GMT',
    'Etc/GMT+0',
    'Etc/GMT+1',
    'Etc/GMT+10',
    'Etc/GMT+11',
    'Etc/GMT+12',
    'Etc/GMT+2',
    'Etc/GMT+3',
    'Etc/GMT+4',
    'Etc/GMT+5',
    'Etc/GMT+6',
    'Etc/GMT+7',
    'Etc/GMT+8',
    'Etc/GMT+9',
    'Etc/GMT-0',
    'Etc/GMT-1',
    'Etc/GMT-10',
    'Etc/GMT-11',
    'Etc/GMT-12',
    'Etc/GMT-13',
    'Etc/GMT-14',
    'Etc/GMT-2',
    'Etc/GMT-3',
    'Etc/GMT-4',
    'Etc/GMT-5',
    'Etc/GMT-6',
    'Etc/GMT-7',
    'Etc/GMT-8',
    'Etc/GMT-9',
    'Etc/GMT0',
    'Etc/Greenwich',
    'Etc/UCT',
    'Etc/Universal',
    'Etc/UTC',
    'Etc/Zulu',
    'Europe/Belfast',
    'Europe/Nicosia',
    'Europe/Tiraspol',
    'Factory',
    'GB',
    'GB-Eire',
    'GMT',
    'GMT+0',
    'GMT-0',
    'GMT0',
    'Greenwich',
    'Hongkong',
    'HST',
    'Iceland',
    'Iran',
    'Israel',
    'Jamaica',
    'Japan',
    'Kwajalein',
    'Libya',
    'MET',
    'Mexico/BajaNorte',
    'Mexico/BajaSur',
    'Mexico/General',
    'MST',
    'MST7MDT',
    'Navajo',
    'NZ',
    'NZ-CHAT',
    'Pacific/Ponape',
    'Pacific/Samoa',
    'Pacific/Truk',
    'Pacific/Yap',
    'Poland',
    'Portugal',
    'PRC',
    'PST8PDT',
    'ROC',
    'ROK',
    'Singapore',
    'Turkey',
    'UCT',
    'Universal',
    'US/Alaska',
    'US/Aleutian',
    'US/Arizona',
    'US/Central',
    'US/East-Indiana',
    'US/Eastern',
    'US/Hawaii',
    'US/Indiana-Starke',
    'US/Michigan',
    'US/Mountain',
    'US/Pacific',
    'US/Pacific-New',
    'US/Samoa',
    'UTC',
    'W-SU',
    'WET',
    'Zulu',
  ];
}
