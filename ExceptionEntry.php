<?php
// This example application requires the Windows Azure SDK for PHP, which can be downloaded here: http://phpazure.codeplex.com/
require_once 'Microsoft\WindowsAzure\Storage\Table.php';

include_once 'storageConfig.php';

// This Windows Azure SDK for PHP maps this class to a table of the same name.
class ExceptionEntry extends Microsoft_WindowsAzure_Storage_TableEntity
{
    /**
     * @azure exceptionmessage
     */
    public $exceptionmessage;
	
	function __construct()
	{
		$this->_partitionKey = date("mdY");
		$this->_rowKey = trim(com_create_guid(), "{}");
	}
	
	function send()
	{
		// If a Microsoft_WindowsAzure_Storage_Table clients are created with no parameters, the Storage Emulator will be used.
		if(PROD_SITE)
			$tableStorageClient = new Microsoft_WindowsAzure_Storage_Table('table.core.windows.net', STORAGE_ACCOUNT_NAME, STORAGE_ACCOUNT_KEY);
		else
			$tableStorageClient = new Microsoft_WindowsAzure_Storage_Table();
		
		// Send message.
		$tableStorageClient->createTableIfNotExists(EXCEPTION_TABLE);
		$tableStorageClient->insertEntity(EXCEPTION_TABLE, $this);
	}
}
?>