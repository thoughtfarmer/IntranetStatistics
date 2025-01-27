<?php
/**
 * Piwik - Open source web analytics
 *
 * @link	http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockLocationProvider.php';

/**
 * Tests w/ 14 visitors w/ 2 visits each.
 * Uses geoip location provider to test city/region reports.
 * 
 * TODO Test ServerBased GeoIP implementation somehow. (Use X-FORWARDED-FOR?)
 * TODO Test PECL implementation somehow. (The PECL module must point to the test dir, not the real one.)
 */
class Test_Piwik_Integration_ManyVisitorsOneWebsiteTest extends IntegrationTestCase
{
	const GEOIP_IMPL_TO_TEST = 'geoip_php';
	
	protected static $idSite = 1;
	protected static $dateTime = '2010-01-03 11:22:33';
	
	public static $ips = array(
		'194.57.91.215', // in Besançon, FR (unicode city name)
		'::ffff:137.82.130.49', // in British Columbia (mapped ipv4)
		'137.82.130.0', // anonymization tests
		'137.82.0.0',
		'2001:db8:85a3:0:0:8a2e:370:7334', // ipv6 (geoip lookup not supported)
		'113.62.1.1', // in Lhasa, Tibet
		'151.100.101.92', // in Rome, Italy (using country DB, so only Italy will show)
		'103.29.196.229', // in Indonesia (Bali), (only Indonesia will show up)
	);
	
	private static $ipsIndex = 0;

	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		try {
			self::setUpWebsitesAndGoals();
			self::downloadGeoIpDbs();
			
			self::setMockLocationProvider();
			self::trackVisits(9, false);
			
			self::setLocationProvider('GeoIPCity.dat');
			self::trackVisits(2, true, $useLocal = false);
			self::trackVisits(4, true, $useLocal = false, $doBulk = true);
			
			self::setLocationProvider('GeoIP.dat');
			self::trackVisits(2, true);
			
			self::trackOtherVisits();
			
			self::setLocationProvider('GeoIPCity.dat');
		} catch(Exception $e) {
			// Skip whole test suite if an error occurs while setup
			throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
		}
	}
	
	public static function tearDownAfterClass()
	{
		self::unsetLocationProvider();
		parent::tearDownAfterClass();
	}
	
	/**
	 * @dataProvider getApiForTesting
	 * @group        Integration
	 * @group        TwoVisitors_TwoWebsites_DifferentDays_ArchivingDisabled
	 */
	public function testApi($api, $params)
	{
		$this->runApiTests($api, $params);
	}

	public function getApiForTesting()
	{
		// Note: we must set  'UserCountry.getLocationFromIP' since it's "excluded" by default in setApiNotToCall
		$apiToCall = array('UserCountry');

		return array(
			array( $apiToCall,
							array(  'idSite'		=> self::$idSite,
									'date'		=> self::$dateTime,
									'periods'	=> array('month'))),

			array($apiToCall, array('idSite'		=> self::$idSite,
									'date'		=> self::$dateTime,
									'periods'	=> array('month'),
									'testSuffix' => '_segment_region',
									'segment'    => 'region==P3;country==gb')),

			array($apiToCall, array('idSite'		=> self::$idSite,
									'date'		=> self::$dateTime,
									'periods'	=> array('month'),
									'testSuffix' => '_segment_city',
									'segment'    => 'city==Stratford-upon-Avon;region==P3;country==gb')),

			array($apiToCall, array('idSite'		=> self::$idSite,
									'date'		=> self::$dateTime,
									'periods'	=> array('month'),
									'testSuffix' => '_segment_lat_long',
									'segment'    => 'lat>45;lat<49.3;long>-125;long<-122')),

			array('UserCountry.getCountry', array('idSite'		=> self::$idSite,
												  'date'		=> self::$dateTime,
												  'periods'		=> array('month'),
												  'testSuffix'	=> '_segment_continent',
												  'segment'   	=> 'continent==eur')),

			array(array('UserCountry.getLocationFromIP', 'Live.getLastVisitsDetails'), array(
														'idSite'		=> self::$idSite,
														 'date'		=> self::$dateTime,
														 'periods'		=> array('month'),
														 'otherRequestParameters' => array('ip' => '194.57.91.215')
													 	)),
		);
	}
	
	public static function setUpWebsitesAndGoals()
	{
		self::createWebsite(self::$dateTime, 0, "Site 1");
        Piwik_Goals_API::getInstance()->addGoal(self::$idSite, 'all', 'url', 'http', 'contains', false, 5);
	}
	
	protected static function trackVisits( $visitorCount, $setIp = false, $useLocal = true, $doBulk = false )
	{
		$dateTime = self::$dateTime;
		$idSite   = self::$idSite;
		
		// use local tracker so mock location provider can be used
		$t = self::getTracker($idSite, $dateTime, $defaultInit = true, $useLocal);
		if ($doBulk)
		{
			$t->enableBulkTracking();
			$t->setTokenAuth(self::getTokenAuth());
		}
		for ($i = 0; $i != $visitorCount; ++$i)
		{
			$t->setNewVisitorId();
			if ($setIp)
			{
				$t->setIp(self::$ips[self::$ipsIndex]);
				++self::$ipsIndex;
			}
			else
			{
				$t->setIp("1.2.4.$i");
			}
			
			// first visit
			$date = Piwik_Date::factory($dateTime)->addDay($i);
			$t->setForceVisitDateTime($date->getDatetime());
			$t->setUrl("http://piwik.net/grue/lair");
			$r = $t->doTrackPageView('It\'s pitch black...');
			if (!$doBulk)
			{
				self::checkResponse($r);
			}
			
			// second visit
			$date = $date->addHour(1);
			$t->setForceVisitDateTime($date->getDatetime());
			$t->setUrl("http://piwik.net/space/quest/iv");
			$r = $t->doTrackPageView("Space Quest XII");
			if (!$doBulk)
			{
				self::checkResponse($r);
			}
		}
		if ($doBulk)
		{
			self::checkResponse($t->doBulkTrack());
		}
	}
	
	protected static function trackOtherVisits()
	{
		$dateTime = self::$dateTime;
		$idSite   = self::$idSite;
		
		$t = self::getTracker($idSite, $dateTime, $defaultInit = true);
		$t->setTokenAuth(self::getTokenAuth());
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addDay(20)->getDatetime());
		$t->setIp('194.57.91.215');
		$t->setCountry('us');
		$t->setRegion('CA');
		$t->setCity('not a city');
		$t->setLatitude(1);
		$t->setLongitude(2);
		$t->setUrl("http://piwik.net/grue/lair");
		self::checkResponse($t->doTrackPageView('It\'s pitch black...'));
	}
	
	public static function setLocationProvider( $file )
	{
		Piwik_UserCountry_LocationProvider_GeoIp::$dbNames['loc'] = array($file);
		Piwik_UserCountry_LocationProvider_GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
		Piwik_UserCountry_LocationProvider::$providers = null;
		Piwik_UserCountry_LocationProvider::setCurrentProvider(self::GEOIP_IMPL_TO_TEST);
		
		if (Piwik_UserCountry_LocationProvider::getCurrentProviderId() !== self::GEOIP_IMPL_TO_TEST)
		{
			throw new Exception("Failed to set the current location provider to '".self::GEOIP_IMPL_TO_TEST."'.");
		}
		
		$possibleFiles = Piwik_UserCountry_LocationProvider_GeoIp::$dbNames['loc'];
		if (Piwik_UserCountry_LocationProvider_GeoIp::getPathToGeoIpDatabase($possibleFiles) === false)
		{
			throw new Exception("The GeoIP location provider cannot find the '$file' file! Tests will fail.");
		}
	}
	
	public static function setMockLocationProvider()
	{
		Piwik_UserCountry_LocationProvider::$providers = null;
		Piwik_UserCountry_LocationProvider::setCurrentProvider('mock_provider');
		MockLocationProvider::$locations = array(
			self::makeLocation('Stratford-upon-Avon', 'P3', 'gb', 123.456, 21.321), // template location
			
			// same region, different city, same country
			self::makeLocation('Nuneaton and Bedworth', 'P3', 'gb'),
			
			// same region, city & country (different lat/long)
			self::makeLocation('Stratford-upon-Avon', 'P3', 'gb', 124.456, 22.231),
			
			// same country, different region & city
			self::makeLocation('London', 'H9', 'gb'),
			
			// same country, different region, same city
			self::makeLocation('Stratford-upon-Avon', 'G5', 'gb'),
			
			// different country, diff region, same city
			self::makeLocation('Stratford-upon-Avon', '66', 'ru'),
			
			// different country, diff region (same as last), different city
			self::makeLocation('Hluboká nad Vltavou', '66', 'ru'),
			
			// different country, diff region (same as last), same city
			self::makeLocation('Stratford-upon-Avon', '66', 'mk'),
			
			// unknown location
			self::makeLocation(null, null, null),
		);
	}
	
	public static function unsetLocationProvider()
	{
		Piwik_UserCountry_LocationProvider::setCurrentProvider('default');
	}
	
	public static function makeLocation( $city, $region, $country, $lat = null, $long = null )
	{
		return array(Piwik_UserCountry_LocationProvider::CITY_NAME_KEY => $city,
					  Piwik_UserCountry_LocationProvider::REGION_CODE_KEY => $region,
					  Piwik_UserCountry_LocationProvider::COUNTRY_CODE_KEY => $country,
					  Piwik_UserCountry_LocationProvider::LATITUDE_KEY => $lat,
					  Piwik_UserCountry_LocationProvider::LONGITUDE_KEY => $long);
	}
}
