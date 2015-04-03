<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ApiBundle\Tests\Functional\DependencyInjection;

use ONGR\ApiBundle\DependencyInjection\Configuration;
use ONGR\ApiBundle\DependencyInjection\ONGRApiExtension;
use ONGR\ApiBundle\Service\DataRequestService;
use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for ONGRApiExtension.
 */
class ONGRApiExtensionTest extends AbstractElasticsearchTestCase
{
    /**
     * @var ONGRApiExtension
     */
    private $extension;

    /**
     * @var string
     *
     * Root name of the configuration.
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extension = $this->getExtension();
        $this->root = 'ongr_api';
        $this->getManager('not_default');
    }

    /**
     * @return ONGRApiExtension
     */
    protected function getExtension()
    {
        return new ONGRApiExtension();
    }

    /**
     * @return ContainerBuilder
     */
    private function getDIContainer()
    {
        $container = new ContainerBuilder();

        return $container;
    }

    /**
     * TODO.
     */
    public function testGetConfig()
    {
        $config = [
            'versions' => [
                'v1' => [
                    'endpoints' => [
                        'persons' => [
                            'manager' => 'es.manager.default',
                            'document' => 'AcmeTestBundle:PersonDocument',
                        ],
                        'people' => [
                            'parent' => 'persons',
                            'manager' => 'es.manager.default',
                            'controller' => 'CustomApi',
                        ],
                    ],
                ],
                'v2' => [
                    'endpoints' => [
                        'people_names' => [
                            'manager' => 'es.manager.default',
                            'document' => 'AcmeTestBundle:PersonDocument',
                            'include_fields' => ['name'],
                        ],
                        'people_surnames' => [
                            'manager' => 'es.manager.default',
                            'document' => 'AcmeTestBundle:PersonDocument',
                            'exclude_fields' => ['name'],
                        ],
                    ],
                ],
            ],
        ];

        $container = $this->getDIContainer();
        $this->extension->load([$config], $container);

        $parameterKey = '.versions';
        $this->assertTrue($container->hasParameter($this->root . $parameterKey));
        $this->assertEquals(['v1', 'v2'], $container->getParameter($this->root . $parameterKey));

        $parameterKey = '.v1.endpoints';
        $this->assertTrue($container->hasParameter($this->root . $parameterKey));
        $this->assertEquals(['persons', 'people'], $container->getParameter($this->root . $parameterKey));

        $parameterKey = '.v1.persons.controller';
        $this->assertTrue($container->hasParameter($this->root . $parameterKey));
        $this->assertEquals('default', $container->getParameter($this->root . $parameterKey));

        $parameterKey = '.v1.people.controller';
        $this->assertTrue($container->hasParameter($this->root . $parameterKey));
        $this->assertEquals('CustomApi', $container->getParameter($this->root . $parameterKey));

        $parameterKey = '.v2.endpoints';
        $this->assertTrue($container->hasParameter($this->root . $parameterKey));
        $this->assertEquals(['people_names', 'people_surnames'], $container->getParameter($this->root . $parameterKey));

        $parameterKey = '.v2.people_names.controller';
        $this->assertTrue($container->hasParameter($this->root . $parameterKey));
        $this->assertEquals('default', $container->getParameter($this->root . $parameterKey));

        $parameterKey = '.v2.people_surnames.controller';
        $this->assertTrue($container->hasParameter($this->root . $parameterKey));
        $this->assertEquals('default', $container->getParameter($this->root . $parameterKey));
    }

    /**
     * Check services are  created.
     */
    public function testDataRequestService()
    {
        $serviceName = ONGRApiExtension::getServiceNameWithNamespace(
            'data_request',
            ONGRApiExtension::getNamespaceName('v1', 'persons')
        );
        /** @var DataRequestService $dataRequest */
        $dataRequest = $this->getContainer()->get($serviceName);
        $this->assertEquals('ONGR\ApiBundle\Service\DataRequestService', get_class($dataRequest));

        $serviceName = ONGRApiExtension::getServiceNameWithNamespace(
            'data_request',
            ONGRApiExtension::getNamespaceName('v1', 'people')
        );
        /** @var DataRequestService $dataRequest */
        $dataRequest = $this->getContainer()->get($serviceName);
        $this->assertEquals('ONGR\ApiBundle\Service\DataRequestService', get_class($dataRequest));

        $request = new Request();
        $request->setMethod('get');
        $request->headers->set('Content-Type', 'application/json');

        $result = $dataRequest->getResponse($request);

        $response = new Response();
        $response->setContent(json_encode([]));
        $response->headers->set('Content-Type', 'application/json');

        $this->assertEquals($response, $result);
    }
}
