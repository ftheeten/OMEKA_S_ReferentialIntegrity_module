<?php
/*
Copyright Franck Theeten, 2025

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.

*/

namespace ReferentialIntegrity\Service\Controller;

use ReferentialIntegrity\Controller\Admin\IndexController;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;


class ManagerControllerFactory implements FactoryInterface
{
	 public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
		$connection = $container->get('Omeka\Connection');
        $entityManager = $container->get('Omeka\EntityManager');
        return new IndexController($connection, $entityManager);
    }
}