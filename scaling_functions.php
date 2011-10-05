<?php
require_once 'Microsoft/WindowsAzure/Storage/Table.php';
require_once 'storageConfig.php';

function ticks_to_time($ticks) {
        return (($ticks - 621355968000000000) / 10000000);
}

function time_to_ticks($time) {
        return number_format(($time * 10000000) + 621355968000000000 , 0, '.', '');
}

function str_to_ticks($str) {
        return time_to_ticks(strtotime($str));
}

function scale_check($metrics) {
	$percent_proc_usage = (isset($metrics['totals']['\Processor(_Total)\% Processor Time']['average'])) ? $metrics['totals']['\Processor(_Total)\% Processor Time']['average'] : null;
	$available_MB_memory = (isset($metrics['totals']['\Memory\Available Mbytes']['average'])) ? $metrics['totals']['\Memory\Available Mbytes']['average'] : null;
	$number_TCPv4_connections = (isset($metrics['totals']['\TCPv4\Connections Established']['average'])) ? $metrics['totals']['\TCPv4\Connections Established']['average'] : null;
    
	if(!is_null($percent_proc_usage)) {
		if( $percent_proc_usage > 75 )
			return 1;
		else if( $percent_proc_usage < 25)
			return -1;
	}

	if(!is_null($available_MB_memory)) {
		if( $available_MB_memory < 25 )
			return 1;
		else if( $available_MB_memory > 1000)
			return -1;
	}
	
	if(!is_null($number_TCPv4_connections)) {
		if( $number_TCPv4_connections > 120 )
			return 1;
		else if( $number_TCPv4_connections < 20)
			return -1;
	}
	
	return 0;
}

// Get the number of instances in the Ready state
function get_num_instances($instances, $role_name) {  
	$count = 0;
	foreach($instances as $instance) {
		if ($instance['rolename'] == $role_name)
			$count++;
	}
	return $count;
}

function get_metrics($deployment_id, $ago = "-15 minutes") {    
	$table = new Microsoft_WindowsAzure_Storage_Table('table.core.windows.net', STORAGE_ACCOUNT_NAME, STORAGE_ACCOUNT_KEY);     
	
	// get DateTime.Ticks in past    
	$ago = str_to_ticks($ago); 
    
	// build query    
	$filter = "PartitionKey gt '0$ago' and DeploymentId eq '$deployment_id'"; 

	// run query    
	$metrics = $table->retrieveEntities('WADPerformanceCountersTable', $filter);     
	
	$arr = array();    
	foreach ($metrics AS $m) {
		// Global totals        
		$arr['totals'][$m->countername]['count'] = (!isset($arr['totals'][$m->countername]['count'])) ? 1 : $arr['totals'][$m->countername]['count'] + 1;        
		$arr['totals'][$m->countername]['total'] = (!isset($arr['totals'][$m->countername]['total'])) ? $m->countervalue : $arr['totals'][$m->countername]['total'] + $m->countervalue;        
		$arr['totals'][$m->countername]['average'] = (!isset($arr['totals'][$m->countername]['average'])) ? $m->countervalue : $arr['totals'][$m->countername]['total'] / $arr['totals'][$m->countername]['count'];         
		
		// Totals by instance        
		$arr[$m->roleinstance][$m->countername]['count'] = (!isset($arr[$m->roleinstance][$m->countername]['count'])) ? 1 : $arr[$m->roleinstance][$m->countername]['count'] + 1;        
		$arr[$m->roleinstance][$m->countername]['total'] = (!isset($arr[$m->roleinstance][$m->countername]['total'])) ? $m->countervalue : $arr[$m->roleinstance][$m->countername]['total'] + $m->countervalue;        
		$arr[$m->roleinstance][$m->countername]['average'] = (!isset($arr[$m->roleinstance][$m->countername]['average'])) ? $m->countervalue : ($arr[$m->roleinstance][$m->countername]['total'] / $arr[$m->roleinstance][$m->countername]['count']);    
	}    
	return $arr;
}
?>