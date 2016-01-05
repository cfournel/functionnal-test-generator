<?php
namespace huitiemesens\FunctionalTestGeneratorBundle\Tests\Controller;

use huitiemesens\FunctionalTestGeneratorBundle\Tests\SetUpFunctionalTest;
/**
 * Generated tests for FunctionalTestGeneratorBundle
 */
class DefaultTest extends SetUpFunctionalTest
{

    /**
     * Set up test
     */
    public function setUp()
    {
        // setup sqlite database via fixtures
        $this->setUpClientAndUser();
    }

    /**
     * Tests the index page
     */
    public function testindex()
    {
        //set up the test. Client is : $this->client;
        $crawler = $this->client->request('GET', '/default');
        $response = $this->client->getResponse();

        // Test page is available (code 2**)
        //print_r($this->client->getResponse()->getContent());die;
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Checks if right controller
        $this->assertEquals('overlord\AppBundle\Controller\CalendarController::indexAction',
            $this->client->getRequest()->attributes->get('_controller'));

        // Test generated content is available (at least one content)
        // TODO : régler le pb de la fonction custom DQL rand() qui  n'est pas loadée
        // (et donc pas de conseils)
        //$this->assertGreaterThan(0, $crawler->filter('#conseils ul li p')->count());

        // Test some static content
        $this->client->reload();

        // Assert that the title is correct
        $html = $crawler->filter('h1')->text();
        $this->assertNotEmpty($html);

        // Test some dynamic content
        // Can't test calendar with phpunit (casper.js ?)
    }


}
?>