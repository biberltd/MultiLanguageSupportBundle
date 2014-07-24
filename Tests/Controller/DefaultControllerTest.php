<?php

namespace BiberLtd\Cores\Bundles\MultiLanguageSupportManagementBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/test/multi_language_support_management_bundle');

        $this->assertTrue($crawler->filter('html:contains("Testing Multi Language Support Management Bundle.")')->count() > 0);
    }
}
