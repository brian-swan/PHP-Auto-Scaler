<?php
ini_set('max_execution_time', 0);
require_once 'scaling_functions.php';
require_once 'Microsoft/WindowsAzure/Management/Client.php';
require_once 'Microsoft/WindowsAzure/Storage/Table.php';
require_once 'ExceptionEntry.php';
require_once 'ScaleStatus.php';
include_once 'storageConfig.php';

$management_client = new Microsoft_WindowsAzure_Management_Client(SUBSCRIPTION_ID, $certificate, 'your_certificate_pass_phrase');

$deployment = $management_client->getDeploymentBySlot(DNS_PREFIX, DEPLOYMENT_SLOT);

while(1) {
	try {
		$metrics = get_metrics($deployment->privateid, AVERAGE_INTERVAL);

		$instance_adjustment = scale_check($metrics);

		$instance_count = get_num_instances($deployment->roleinstancelist, ROLE_NAME);

		switch ($instance_adjustment) {    
			case 1:	
				if($instance_count < MAX_INSTANCES) {
					// Add an instance  
					$scale_status = new ScaleStatus();
					$scale_status ->statusmessage = "Scaling out.";
					$scale_status ->send();
					unset($scale_status );
					$management_client->setInstanceCountBySlot(DNS_PREFIX, DEPLOYMENT_SLOT, ROLE_NAME, $instance_count + 1);
					$management_client->waitForOperation();
				} else {
					$scale_status = new ScaleStatus();
					$scale_status ->statusmessage = "Need to scale out, but max_instances has been reached.";
					$scale_status ->send();
					unset($scale_status );
				}
				break;    
			case 0:        
				// Do no add/remove an instances 
				$scale_status = new ScaleStatus();
				$scale_status ->statusmessage = "Perfomance within acceptable range.";
				$scale_status ->send();
				unset($scale_status );
				break;    
			case -1:
				if($instance_count > MIN_INSTANCES) {
					// Remove an instance
					$scale_status = new ScaleStatus();
					$scale_status ->statusmessage = "Scaling back.";
					$scale_status ->send();
					unset($scale_status );
					$management_client->setInstanceCountBySlot(DNS_PREFIX, DEPLOYMENT_SLOT, ROLE_NAME, $instance_count - 1);
					$management_client->waitForOperation();
				} else {
					$scale_status = new ScaleStatus();
					$scale_status ->statusmessage = "Need to scale back, but min_instances has been reached.";
					$scale_status ->send();
					unset($scale_status );
				}
				break;
		} 
	}
	catch (Exception $e) {
		$ex = new ExceptionEntry();
		$ex->exceptionmessage = "Exception caught (A): ". $e->getMessage();
		$ex->send();
		sleep(10);
		unset($ex);
	}
	
	try {
		// Refresh deployment info.
		$deployment = $management_client->getDeploymentBySlot(DNS_PREFIX, DEPLOYMENT_SLOT);
		
		// Refresh instance count.
		$instance_count = get_num_instances($deployment->roleinstancelist, ROLE_NAME);
		
		// Do not proceed until all instances are in "Ready" state.
		$ready_count = 0;
		while($ready_count != $instance_count) {
			$ready_count = 0;
			foreach($deployment->roleinstancelist as $instance) {
				if ($instance['rolename'] == ROLE_NAME && $instance['instancestatus'] == 'Ready')
					$ready_count++;
			}
			sleep(10); // Avoid being too chatty.
			$scale_status = new ScaleStatus();
			$scale_status ->statusmessage = "Checking instances. Ready count = " . $ready_count . ". Instance count = " . $instance_count;
			$scale_status ->send();
		}
		
		sleep(COLLECTION_FREQUENCY); 
		
	} catch (Exception $e) {
		$ex = new ExceptionEntry();
		$ex->exceptionmessage = "Exception caught (B): ". $e->getMessage();
		$ex->send();
		sleep(10);
		unset($ex);
	}
}
?>