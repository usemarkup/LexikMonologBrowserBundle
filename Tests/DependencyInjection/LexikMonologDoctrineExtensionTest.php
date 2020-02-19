<?php

namespace Lexik\Bundle\MonologBrowserBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lexik\Bundle\MonologBrowserBundle\DependencyInjection\LexikMonologBrowserExtension;

class LexikMonologBrowserExtensionTest extends TestCase
{
    public function testConfigLoad()
    {
        $extension = new LexikMonologBrowserExtension();
        $extension->load(array($this->getConfig()), $container = new ContainerBuilder());

        // parameters
        $this->assertEquals('test_layout.html.twig', $container->getParameter('lexik_monolog_browser.base_layout'));
        $this->assertEquals('logs', $container->getParameter('lexik_monolog_browser.doctrine.table_name'));

        // services
        $this->assertTrue($container->hasDefinition('lexik_monolog_browser.doctrine_dbal.connection'));
        $this->assertTrue($container->hasDefinition('lexik_monolog_browser.handler.doctrine_dbal'));
    }

    protected function getConfig()
    {
        return array(
            'base_layout' => 'test_layout.html.twig',
            'doctrine'    => array(
                'table_name' => 'logs',
                'connection' => array(
                    'driver' => 'pdo_sqlite',
                    'dbname' => 'monolog',
                    'memory' => true,
                ),
            ),
        );
    }
}
