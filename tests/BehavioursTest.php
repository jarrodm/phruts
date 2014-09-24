<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Phruts\Util\ModuleProvider\FileCacheModuleProvider;

class BehavioursTest extends \Silex\WebTestCase
{

    public function testConfigPaths()
    {
        $client = $this->createClient();
        $client->followRedirects(true);

        // Welcome path
        $crawler = $client->request('GET', '');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome")'));

        $crawler = $client->request('GET', '/resourceA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));

        $crawler = $client->request('GET', '/resourceB');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        $crawler = $client->request('GET', '/resourceC');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome")'));

        // Test the module
        $crawler = $client->request('GET', '/moduleA/resourceD');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        $crawler = $client->request('GET', '/moduleA/resourceA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        // Test merging of configs
        $crawler = $client->request('GET', '/moduleB/resourceA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));

        $crawler = $client->request('GET', '/moduleB/resourceB');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        $crawler = $client->request('GET', '/moduleB/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome")'));

        $crawler = $client->request('GET', '/moduleB/resourceE');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));
    }


    public function testActionForms()
    {
        // Test that the 'reset' function is called first
    }

    public function testActionFormValidate()
    {

    }

    public function testExceptionHandler()
    {

    }

    public function testFormBeanProperties()
    {

    }


    public function testCustomRequestProcessor()
    {

    }

    public function testPlugin()
    {

    }

    public function testDataSource()
    {

    }

    public function testRoles()
    {

    }

    public function testActionMessages()
    {

    }

    public function testMessageResources()
    {

    }

    public function testCustomActionConfig()
    {
        // Test that properties can be set from the action config
    }

    public function testTwigExtensions()
    {

    }

    public function createApplication()
    {
        // Create a silex application
        $app = new Silex\Application();

        // Configure
        $app['debug'] = true;
        $app['exception_handler']->disable();
        $app['session.test'] = true;

        // Add in phruts to organise your controllers
        $app->register(new Phruts\Provider\PhrutsServiceProvider(), array(
                // Register our modules and configs
                Phruts\Util\Globals::ACTION_KERNEL_CONFIG => array(
                    'config' => __DIR__  . '/Resources/module1-config.xml',
                    'config/moduleA' => __DIR__ . '/Resources/module2-config.xml',
                    'config/moduleB' => __DIR__ . '/Resources/module2-config.xml,' .
                        __DIR__ . '/Resources/module1-config.xml'
                )
            ));

        // Setup the mock file system for caching
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('cacheDir'));
        $app[Phruts\Util\Globals::MODULE_CONFIG_PROVIDER] = $app->share(function () use ($app) {
                $provider = new FileCacheModuleProvider($app);
                $provider->setCachePath(vfsStream::url('cacheDir'));
                return $provider;
            });

        // Add a relevant html
        $app->get('{path}', function($path) use ($app) {
                return new \Symfony\Component\HttpFoundation\Response(file_get_contents(__DIR__ . '/Resources/' . $path));
            })
            ->assert('path', '.+\.html');

        // Add routes to be matched by Phruts
        $app->get('{path}', function (Request $request) use ($app) {
                return $app[Phruts\Util\Globals::ACTION_KERNEL]->handle($request, HttpKernelInterface::SUB_REQUEST, false);
            })
            ->assert('path', '.*')
            ->value('path', '/'); // Set the welcome path

        return $app;
    }
}
 