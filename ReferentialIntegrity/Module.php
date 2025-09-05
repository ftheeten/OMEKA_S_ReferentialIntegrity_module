<?php
/*
Copyright Franck Theeten, 2025

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.

*/

namespace ReferentialIntegrity;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\ServiceManager\ServiceLocatorInterface;

use Omeka\Api\Representation\ItemRepresentation;
use ReferentialIntegrity\Exception\ReferentialIntegrityException;

class Module extends AbstractModule
{
	
	
	
	public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }



   public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
			\Omeka\Api\Adapter\ItemAdapter::class,
            'api.delete.pre',
            [$this, 'beforeDeleteItem']
        );
    }


    public function install(ServiceLocatorInterface $serviceLocator): void
    {
        $connection =$serviceLocator->get('Omeka\Connection');
        $connection->exec("
            CREATE TABLE IF NOT EXISTS referential_integrity (
                id INT AUTO_INCREMENT PRIMARY KEY,
				source_template_id INT NOT NULL REFERENCES resource_template(id),
                source_property_id INT NOT NULL REFERENCES resource_template_property(property_id ),
				target_template_id INT NOT NULL REFERENCES resource_template(id),
                block_on_delete boolean  DEFAULT TRUE,
				cascade_delete boolean  DEFAULT FALSE,
				username VARCHAR(1000),
				creation_date TIMESTAMP  DEFAULT  CURRENT_TIMESTAMP,
				UNIQUE KEY referential_integrity_all_unique (source_template_id, source_property_id, target_template_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator): void
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec("DROP TABLE IF EXISTS referential_integrity;");
    }

   
	
	 public function beforeDeleteItem($event): void
    {
		
		//$services = $this->getServiceLocator();
		//$logger = $services->get('Omeka\Logger');
		//$logger->debug("Tried to catch delete");
		 //$id = $event->getParam("id");
		/* $adapter = $event->getParam("adapter");
		 
		 $entity = $adapter->getEntityManager()->find(
			$adapter->getEntityClass(),
			$id
		 );*/
		 //$keys=array_keys( $event->getParams());
		 
		 $services = $event->getTarget()->getServiceLocator();

		 $api = $services->get('Omeka\ApiManager');
		 $connection=$services->get('Omeka\Connection');
		 //$manager = $services->get('Omeka\EntityManager');
		 $request=$event->getParam("request");
		 $resource= $request->getResource();
		 $id=$request->getId();
		 $response=$api->read($resource, $id);
		 $item=$response->getContent();
		 $target_template_id=$item->resourceTemplate()->id();
		 $sql="WITH 
				init_check AS
				(
					SELECT source_template_id, resource_template.label as source_template_label, source_property_id FROM referential_integrity 
					INNER JOIN resource_template ON referential_integrity.source_template_id=resource_template.id 
					INNER JOIN resource_template_property ON resource_template.id=resource_template_property.resource_template_id AND referential_integrity.source_property_id=resource_template_property.property_id 
					WHERE target_template_id=:targettemplateid
				),
				a 
				AS
				(
				SELECT source_template_label, count(*) as cpt FROM value 
					INNER JOIN init_check  ON value.property_id=init_check.source_property_id
					INNER JOIN resource ON value.value_resource_id=resource.id
					WHERE  value.value_resource_id=:id_obj
					GROUP BY  source_template_label)
					SELECT * FROM a;";
					
			$stmt = $connection->prepare($sql);
			$stmt->bindValue('targettemplateid', $target_template_id);
			$stmt->bindValue('id_obj', $id);
			$stmt->execute();
			$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$helper_exception=[];
			if(count($rows)>0)
			{
				foreach($rows as $row)
				{
					if($row["cpt"]>0)
					{
						$helper_exception[]= (string)$row["cpt"]." object(s) associated in template ".$row["source_template_label"];
					}
				}
				if(count($helper_exception)>0)
				{
					throw new ReferentialIntegrityException("Can't delete ".$item->title()." due to constraint defined in the ReferentialIntegrity module. Here is the list : ".implode("; ", $helper_exception));
				}
			}
            
		
    }
	
	public function handleViewLayout(Event $event): void
    {
        /** @var \Laminas\View\Renderer\PhpRenderer $view */
        $view = $event->getTarget();

        $params = $view->params()->fromRoute();
        $action = $params['action'] ?? null;
        if ($action !== 'browse') {
            return;
        }

        $vars = $view->vars();

        $html = $view->hyperlink($view->translate('Create an integrity constraint'), $view->url('admin/referential-integrity'), ['class' => 'button']);
        $content = $vars->offsetGet('content');
        $content = str_replace('<div id="page-actions">', '<div id="page-actions">' . PHP_EOL . $html, $content);
        $vars->offsetSet('content', $content);
    }
}