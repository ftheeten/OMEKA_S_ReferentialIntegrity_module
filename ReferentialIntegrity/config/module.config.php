<?php
/*
Copyright Franck Theeten, 2025

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.

*/
namespace ReferentialIntegrity;


return [
	'controllers' => [
        'invokables' => [
            //'ReferentialIntegrity\Controller\Admin\Index' => Controller\Admin\IndexController::class,
           // 'Solr\Controller\Admin\SearchField' => Controller\Admin\SearchFieldController::class,
           // 'Solr\Controller\Admin\Transformations' => Controller\Admin\TransformationsController::class,
        ],
        'factories' => [
				'ReferentialIntegrity\Controller\Admin\Manager'=>Service\Controller\ManagerControllerFactory::class,
            //'Solr\Controller\Admin\Mapping' => Service\Controller\MappingControllerFactory::class,
            //'Solr\Controller\Api' => Service\Controller\ApiControllerFactory::class,
           // 'Solr\Controller\ApiLocal' => Service\Controller\ApiLocalControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'referential_integrity_service' => \ReferentialIntegrity\Service\ReferentialIntegrityService::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
             dirname(__DIR__) . '/view',
        ],
    ],
	'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Referential Integrity',
                'route' => 'admin/referential-integrity',
                'resource' => 'ReferentialIntegrity\Controller\Admin\Manager',
                //'privilege' => 'browse',
                //'class' => 'o-icon-search',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
				'child_routes' => [
					"referential-integrity"=>[
						'type' => 'Segment',
						'options' => [
							'route' => '/referential-integrity[/:action[/:id]]',
							'defaults' => [
								'__NAMESPACE__' => 'ReferentialIntegrity\Controller',
								'controller' => 'ReferentialIntegrity\Controller\Admin\Manager',
								'action' => 'index',
							],
						],
					],
				],
            ],
        ],
    ],
	"form_elements" => [
		"factories" => [
			Form\ReferentialIntegrity::class => \Omeka\Form\Factory\InvokableFactory::class,
		]
	]
	
];