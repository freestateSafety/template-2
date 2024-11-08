<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testDashboard()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/dashboard');
    }

    public function testPassword()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/password');
    }

}
