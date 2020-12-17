<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GrantedRoutesTest extends WebTestCase
{
    /**
     * @dataProvider provideRedirectLoginUrls
     */
    public function testRedirectToLogin($url)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url);
        $this->assertResponseRedirects('/login');
    }

    /**
     * @dataProvider provideDeniedUrls
     */
    public function testAccesDenied($url)
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('azerty@gmail.com');

        $client->loginUser($testUser);
        $crawler = $client->request('GET', $url);
        $this->assertResponseStatusCodeSame('403');
    }

    /**
     * @dataProvider provideNotLoggedUrls
     */
    public function testResponseSuccessNotLogged($url)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @dataProvider provideLoggedUrls
     */
    public function testResponseSuccessLogged($url)
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('azerty@gmail.com');

        $client->loginUser($testUser);
        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    public function provideRedirectLoginUrls()
    {
        return [
            ['/tricks/2/edit'],
            ['/comment/11/edit'],
            ['/tricks/new'],
            ['/dashboard'],
        ];
    }

    public function provideDeniedUrls()
    {
        return [
            ['/tricks/2/edit'],
            ['/comment/2/edit'],
        ];
    }

    public function provideNotLoggedUrls()
    {
        return [
            ['/'],
            ['/register'],
            ['/conditions-d-utilisation'],
            ['/tricks/1-mon-trick-numero-1'],
        ];
    }

    public function provideLoggedUrls()
    {
        return [
            ['/'],
            ['/register'],
            ['/conditions-d-utilisation'],
            ['/comment/3/edit'],
            ['/dashboard'],
            ['/tricks/new'],
            ['/tricks/1-mon-trick-numero-1'],
            ['/tricks/3/edit'],

        ];
    }
}