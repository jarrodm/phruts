<?php
namespace ActionTest;

use Phruts\Action\AbstractActionForm;
use Phruts\Action\ActionError;
use Phruts\Action\ActionErrors;
use Phruts\Action\ActionMapping;
use Phruts\Action\RequestDispatcherMatcher;
use Phruts\Config\FormBeanConfig;
use Phruts\Config\ModuleConfig;
use Phruts\Util\ClassLoader;
use Phruts\Config\ActionConfig;
use Phruts\Config\ForwardConfig;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phruts\Action\RequestProcessor
     */
    protected $requestProcessor;
    protected $request;
    /**
     * @var Response;
     */
    protected $response;

    /**
     * @var \Phruts\Config\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Phruts\Action\ActionMapping
     */
    protected $actionConfig1;

    protected $actionKernel;
    protected $application;

    public function setUp()
    {

        $this->request = new \Symfony\Component\HttpFoundation\Request();
        $storage = new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);
        $this->request->setSession($session);
        $this->request->initialize();
        $this->response = new \Symfony\Component\HttpFoundation\Response();


        $this->moduleConfig = new \Phruts\Config\ModuleConfig('');
        $actionConfig = new \Phruts\Config\ActionConfig();
        $actionConfig->setPath('/default');
        $actionConfig->setType('\Phruts\Actions\ForwardAction');
        $this->moduleConfig->addActionConfig($actionConfig);

        $controllerConfig = new \Phruts\Config\ControllerConfig();
        $controllerConfig->setLocale('fr');
        $controllerConfig->setContentType('application/x-javascript');
        $this->moduleConfig->setControllerConfig($controllerConfig);

        // Add a default action mapping
        $this->actionConfig1 = new ActionMapping();
        $this->actionConfig1->setPath('/mypath');
        $this->actionConfig1->setType('\Phruts\Action\Action');
        $forwardConfig = new ForwardConfig();
        $forwardConfig->setName('success');
        $forwardConfig->setPath('success.html.twig');
        $this->actionConfig1->setModuleConfig($this->moduleConfig);
        $this->moduleConfig->addActionConfig($this->actionConfig1);

//        $this->application = new \Silex\Application();
        $this->application = $this->getMock('\Silex\Application', array('handle'));
        $this->application->expects($this->any())
            ->method('handle')
            ->willReturn(new Response());

        $this->actionKernel = new \Phruts\Action\ActionKernel($this->application);

        $this->requestProcessor = new \Phruts\Action\RequestProcessor();
        $this->requestProcessor->init($this->actionKernel, $this->moduleConfig);

    }


    public function testProcessPath()
    {
        $request = Request::create('http://localhost/test', 'GET', array(), array(), array(), array('PATH_INFO' => '/test'));

        $method = self::getMethod('processPath');

        $result = $method->invokeArgs($this->requestProcessor, array($request, $this->response));
        $this->assertNotEmpty($result);
        $this->assertNotEquals(400, $this->response->getStatusCode());
        $this->assertEquals('/test', $result);
    }

    public function testProcessLocale()
    {
        $method = self::getMethod('processLocale');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
        $this->assertEquals('en', $this->request->getSession()->get(\Phruts\Util\Globals::LOCALE_KEY));
    }

    public function testProcessContent()
    {
        $method = self::getMethod('processContent');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
        $this->assertEquals($this->moduleConfig->getControllerConfig()->getContentType(), $this->response->headers->get('content-type'));
    }

    public function testProcessException()
    {
        $method = self::getMethod('processException');

        $exception = new \Exception();
        $form = null;
        $mapping = $this->actionConfig1;

        $this->setExpectedException('\Exception');
        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $exception, $form, $mapping));
    }

    public function testProcessNoCache()
    {
        $method = self::getMethod('processNoCache');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
    }

    public function testProcessPreprocess()
    {
        $method = self::getMethod('processPreprocess');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
    }

    public function testProcessMapping()
    {
        $method = self::getMethod('processMapping');

        $mapping = $this->actionConfig1;

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\BadRequestHttpException');
        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessRoles()
    {
        $method = self::getMethod('processRoles');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessActionForm()
    {
        $method = self::getMethod('processActionForm');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessPopulate()
    {
        $method = self::getMethod('processPopulate');

        $mapping = $this->actionConfig1;
        $form = null;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $form, $mapping));
    }

    public function testProcessValidate()
    {
        $method = self::getMethod('processValidate');

        $mapping = $this->actionConfig1;
        $form = null;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $form, $mapping));
    }

    public function testProcessForward()
    {
        $method = self::getMethod('processForward');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessInclude()
    {
        $method = self::getMethod('processInclude');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessActionCreate()
    {
        $method = self::getMethod('processActionCreate');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessActionPerform()
    {
        $method = self::getMethod('processActionPerform');

        $mapping = $this->actionConfig1;
        $action = ClassLoader::newInstance($this->actionConfig1->getType(), '\Phruts\Action\Action');
        $form = null;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $action, $form, $mapping));
    }

    public function testProcessForwardConfig()
    {
        $method = self::getMethod('processForwardConfig');

        $mapping = $this->actionConfig1;
        $forward = $mapping->findForward('success');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $forward));
    }

    public function testDoForward()
    {
        $method = self::getMethod('doForward');
        $uri = 'index.html';

        $method->invokeArgs($this->requestProcessor, array($uri, $this->request, $this->response));
    }

    public function testDoInclude()
    {
        $method = self::getMethod('doInclude');
        $uri = 'index.html';

        $method->invokeArgs($this->requestProcessor, array($uri, $this->request, $this->response));
    }

    public function testProcess()
    {
        // Mock a request
        $request = Request::create('http://localhost/test', 'GET', array(), array(), array(), array('PATH_INFO' => '/test'));

        // Setup a base action configuration to forward a success
        $actionConfig = new ActionMapping();
        $actionConfig->setPath('/test');
        $actionConfig->setType('\Phruts\Actions\ForwardAction');
        $actionConfig->setParameter('success');
        $actionConfig->setModuleConfig($this->moduleConfig);
        $this->moduleConfig->addActionConfig($actionConfig);
        $forwardConfig = new ForwardConfig();
        $forwardConfig->setName('success');
        $forwardConfig->setPath('file.html');
        $actionConfig->addForwardConfig($forwardConfig);

        $this->requestProcessor->process($request, $this->response);
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function testInvalidActionForm()
    {
        // Mock a request
        $request = Request::create('http://localhost/test', 'GET', array(), array(), array(), array('PATH_INFO' => '/test'));

        $formConfig = new FormBeanConfig();
        $formConfig->setName('form1');
        $formConfig->setType('\ActionTest\MyInvalidForm');
        $this->moduleConfig->addFormBeanConfig($formConfig);
        $actionMapping = new ActionMapping();
        $actionMapping->setScope('request');
        $actionMapping->setPath('/test');
        $actionMapping->setType('\Phruts\Actions\ForwardAction');
        $actionMapping->setParameter('success');
        $actionMapping->setName('form1');
        $actionMapping->setInput('myinput.html.twig');
        $forwardConfig = new ForwardConfig();
        $forwardConfig->setName('success');
        $forwardConfig->setPath('success.html.twig');
        $actionMapping->addForwardConfig($forwardConfig);
        $actionMapping->setModuleConfig($this->moduleConfig);
        $this->moduleConfig->addActionConfig($actionMapping);

        $this->requestProcessor->process($request, $this->response);
    }

//    public function testNextActionForward()
//    {
//        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
//        $request->initialize();
//        $request->expects($this->exactly(1))
//            ->method('getPathInfo')
//            ->willReturn('/test');
//
//        // Update the mock
//        $dispatcher = $this->getMock('\Phruts\Action\RequestDispatcher');
//        $dispatcher->expects($this->exactly(1))
//            ->method('doInclude')
//            ->willReturn(null);
//        $this->application['request_dispatcher'] = $dispatcher;
//
//        // Setup a base action configuration to forward a success
//        $actionConfig = new ActionMapping();
//        $actionConfig->setPath('/test');
//        $actionConfig->setType('\ActionTest\MockAction');
//        $actionConfig->setModuleConfig($this->moduleConfig);
//        $this->moduleConfig->addActionConfig($actionConfig);
//        $forwardConfig = new ForwardConfig();
//        $forwardConfig->setName('success');
//        $forwardConfig->setPath('file.html');
//
//        $this->requestProcessor->process($request, $this->response);
//    }



    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('\Phruts\Action\RequestProcessor');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

}

class MyInvalidForm extends AbstractActionForm
{
    public function validate(\Phruts\Action\ActionMapping $mapping, \Symfony\Component\HttpFoundation\Request $request)
    {
        $errors = new ActionErrors();
        $errors->add('property', new ActionError('message'));
        return $errors;
    }


}
