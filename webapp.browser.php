<?php

namespace gamesolids;

/**
 * WebappBrowser uses $_POST data to format a file information request
 * 
 */

class WebappBrowser {

	protected $dropbox = NULL;
	private $currentPath = "/";
	private $currentMethod = "allFiles";

	public function __construct(){
		require 'webapp.base.php';
		$this->dropbox = new WebappBase();
	}


	/**
	 * Get folder information from requested path.
	 * 
	 * @return array An associaciative array with folder content data or error information.
	 */
	private function getFolder(){

		if (isset($_POST["get"])) {
			// containers
			$shareData = array();
			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );
			// store post vars
			$this->currentMethod = $_POST['get'];
			$this->currentPath = $_POST['path'];

			// filter return based on request type
			try{
				switch ($this->currentMethod) {
					case 'allFiles':
						// show all file types in current path
						$listFolderContents = $this->dropbox->getFolderContents( $this->currentPath );
						foreach ($listFolderContents['response']['content'] as $key => $value) {
							$shareData[$key] = $this->dropbox->getFileMeta( $listFolderContents['response']['content'][$key]["path_display"] );
						}
						break;
					
					default:
						// code...
						$itemData[0] = array('message' => "No request data received.", );
						break;
				}

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'The request was received.';
				$response['response']['content'] = array('dropboxResponse' => $shareData, );

			}  catch (\Exception $e) {
				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem locating that directory.';
				$response['response']['content'] = array('errorText' => $data, );
			
			}  // end try

		} else{
			// if no path was set
			$data = array( 'error_summary' => "file/not_found/", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "not_found", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'We received your request, but header was no data.';
			$response['response']['content'] = array('errorText' => $data, );
		
		}// end if isset

		return $response;
	} // end getFolder
} // end class


$gsBrowser = new WebappBrowser();

if (isset($_POST["get"])) {
	header('Content-Type: application/json');
	echo json_encode($gsBrowser -> getFolder());
}
