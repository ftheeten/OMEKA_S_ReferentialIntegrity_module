<?php

/*
Copyright Franck Theeten, 2025

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.

*/

namespace ReferentialIntegrity\Controller\Admin;


use ReferentialIntegrity\Form\ConfigForm;


use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
//use Omeka\Entity\ResourceClass;
use Omeka\Entity\ResourceTemplate;

use Omeka\Entity\Property;
use Omeka\Form\Element\ResourceTemplateSelect;
use Omeka\Form\Element\PropertySelect;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Laminas\Form\Form;
use Doctrine\DBAL\Exception as DBALException;


class IndexController extends AbstractActionController
{

    public function __construct(Connection $connection, EntityManager $entityManager)
    {
		$this->connection = $connection;
        $this->entityManager = $entityManager;
    }
	
		public function indexAction()
	{
		$sql="WITH a AS
( SELECT referential_integrity.id,  resource_template.label as source_template_label, property.label as source_property_label, target_template_id, username, creation_date FROM referential_integrity
 LEFT  JOIN
resource_template ON referential_integrity.source_template_id= resource_template.id
LEFT  JOIN
property ON referential_integrity.source_property_id= property.id), b
as (
SELECT a.id, source_template_label, source_property_label, resource_template.label as target_template_label, username, creation_date FROM a
 LEFT  JOIN
resource_template ON target_template_id= resource_template.id
)
SELECT * FROM b ORDER BY creation_date"; 
		$rows = $this->connection->fetchAllAssociative($sql);
		return new ViewModel(["rows"=> $rows]);
	}

  
	
	   public function addAction()
    {
		
			$form=$this->getForm(ConfigForm::class);
			$templates = $this->api()->search('resource_templates',    ['is_public' => null], ['sort_by' => 'label'])->getContent();
		
			if ($this->getRequest()->isPost()) 
			{								
				$constraints=$this->params()->fromPost("constraint_params", []);
				
				if(count($constraints)>0)
				{
					
					$services = $this->getEvent()->getApplication()->getServiceManager();
					$user = $this->identity();
					foreach($constraints as $cons)
					{
						print_r($cons);
						$c_a=json_decode($cons, $assoc = true);
						
						//$connection = $services->get('Omeka\Connection');
						try
						{
							$this->connection->insert('referential_integrity', [
								'source_template_id ' => $c_a["template_src"],
								'source_property_id  ' => $c_a["property_src"],
								'target_template_id ' => $c_a["template_target"],
								"block_on_delete" => true,
								"username"=>$user->getName()
							]);
							$this->messenger()->addSuccess('Constraint added');
							return $this->redirect()->toRoute('admin/referential-integrity', ['action' => 'index']);
						}
						catch (DBALException $e)
						{
							// Erreur SQL (conflit, clé manquante, etc.)
							$this->messenger()->addError('Error DBAL : ' . $e->getMessage());
						}
						catch (Exception $e)
						{
							// Erreur SQL (conflit, clé manquante, etc.)
							$this->messenger()->addError('Error  : ' . $e->getMessage());
						}
					
					}
				}
				
				
			}
			
			return new ViewModel([
			'form' => $form,
            'templates' => $templates ,
			
			]);
    }
	
	public function getPropertiesAction()
	{
		
		//http://172.16.11.149/omeka-s/admin/referential-integrity/get-properties?template_id=2
		//http://172.16.11.149/omeka-s/api/items/56649
		$this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/json');

		$template_id= (int) $this->params()->fromQuery('template_id', 0);
		$template = $this->api()->read('resource_templates', $template_id)->getContent();
		$properties = $template->resourceTemplateProperties();
		$returned=[];
		 foreach ($properties as $p) 
		 { 
			$returned[]=["id"=>$p->property()->id(), "label"=>$p->property()->label() ];
				
         }
		 return $this->getResponse()->setContent(json_encode($returned));
		 
	}
	

	
	public function deleteAction()
	{
		$id = (int) $this->params()->fromRoute('id');

		if (!$id) {
			$this->messenger()->addError("ID manquant pour la suppression.");
			return $this->redirect()->toRoute('admin/my-module', ['action' => 'browse']);
		}

		try {
			$this->connection->delete('referential_integrity', ['id' => $id]);
			$this->messenger()->addSuccess("Relation #$id supprimée.");
		} catch (\Doctrine\DBAL\Exception $e) {
			$this->messenger()->addError('Erreur SQL : ' . $e->getMessage());
		}

		// Redirection après action
		return $this->redirect()->toRoute('admin/referential-integrity', ['action' => 'index']);
	}
	
	
}