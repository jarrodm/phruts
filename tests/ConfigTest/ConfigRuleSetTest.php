<?php
namespace ConfigTest;

use Phigester\Digester;
use Phruts\Config\ConfigRuleSet;
use Phruts\Config\ModuleConfig;

class ConfigRuleSetTest extends \PHPUnit_Framework_TestCase
{

    public function testRuleSets()
    {
        $digester = new Digester();
        $digester->addRuleSet(new ConfigRuleSet('phruts-config'));
        $moduleConfig = new ModuleConfig('');
        $digester->push($moduleConfig);
        $digester->parse(__DIR__ . '/../Resources/example-config.xml');
        $this->assertTrue(count($moduleConfig->findActionConfigs()) > 0);
        $this->assertNotEmpty($moduleConfig->findActionConfig('/login'));
    }
}
 