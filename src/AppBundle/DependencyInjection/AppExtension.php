<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppExtension extends Extension implements PrependExtensionInterface
{

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');

        // determine if SupervisorBundle is registered
        if (!isset($bundles['SupervisorBundle'])) {
            // process the configuration of AcmeHelloExtension
            $configs = $container->getExtensionConfig($this->getAlias());
            // use the Configuration class to generate a config array
            $config = $this->processConfiguration(new Configuration(), $configs);

            $servers = [];
            if (isset($config['supervisor_hostname'])) {
                if (preg_match('|^%(.*)%$|', $config['supervisor_hostname'], $matches)) {
                    $hostname = $container->getParameter($matches[1]);
                } else {
                    $hostname = $config['supervisor_hostname'];
                }
                $records = @dns_get_record($hostname, DNS_A);

                if ($records !== FALSE) {
                    $index = 0;
                    foreach ($records as $record) {
                        $curr = sprintf('%02d', $index++);
                        $servers["supervisor_$curr"] = [
                            'host' => $record['ip'],
                            'port' => $config['supervisor_port'],
                            'username' => $config['supervisor_username'],
                            'password' => $config['supervisor_password'],
                        ];
                    }
                } else {
                    $servers["supervisor_00"] = [
                        'host' => '127.0.0.1',
                        'port' => $config['supervisor_port'],
                        'username' => $config['supervisor_username'],
                        'password' => $config['supervisor_password'],
                    ];
                }
            }

            if (count($servers) > 0) {
                $supervisorConfig = [
                    'default_environment' => 'auto',
                    'servers' => [
                        'auto' => $servers
                    ]
                ];

                $container->prependExtensionConfig('yz_supervisor', $supervisorConfig);
            }
        }
    }
}