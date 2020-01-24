<?php

namespace gamesolids;

/**
 * upload to dropbox
 */

class webappUploader {

	private $dropbox = NULL;

	function __construct(){
		require 'webapp.base.php';
		$this->dropbox = new webappBase();
	}

	


	/**
	 * Upload a file from POST data.
	 * 
	 * @return array An associaciative array with uploaded file data or error information.
	 */
	public function uploadFile(){

		// Check if file was uploaded
		if (isset($_FILES["uploadFile"])) {
			// File to Upload
			$file = $_FILES['uploadFile'];

			// File object needed for new file
			$postData = array('path' => $file['tmp_name'], 'name'=> $file['name'], );

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			try {

				// Upload the file to Dropbox
				$uploadedFile = $this->dropbox->newFile( $postData, $_POST['uploadPath'] );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Received request to upload file.';
				$response['response']['content'] = array('dropboxResponse' => $uploadedFile, );

			} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem uploading the file.';
				$response['response']['content'] = array('errorText' => $data, );
			
			} // end try
		} else {
			// if no file was sent
			$data = array( 'error_summary' => "Unable to complete request.", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "not_found", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'We received your request, but header was no data.';
			$response['response']['content'] = array('errorText' => $data, );
		
		} // end if isset

		return $response;
	} // end uploadFile()


	/**
	 * Get sharing information from uploaded file.
	 * 
	 * @return array An associaciative array with file share data or error information.
	 */
	public function getUploadShare(){

		// Check if file was uploaded
		if (isset($_POST["sharePath"])) {

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			try {

				// Upload the file to Dropbox
				$shareData = $this->dropbox->getSharingInfo( $_POST["sharePath"] , true );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Received request to get share data.';
				$response['response']['content'] = array('dropboxResponse' => $shareData, );

			} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem retrieving share info.';
				$response['response']['content'] = array('errorText' => $data, );
			
			} // end try
		} else {
			// if no file was sent
			$data = array( 'error_summary' => "Unable to complete request.", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "not_found", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'We received your request, but header was no data.';
			$response['response']['content'] = array('errorText' => $data, );
		
		} // end if isset

		return $response;
	}



	/**
	 * Get full directory listing as path list.
	 * 
	 * @return array An associaciative array with file share data or error information.
	 */
	public function getFolderList(){

		// Check if file was uploaded
		if (isset($_POST["folderList"])) {

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			try {

				// Upload the file to Dropbox
				$folderData = $this->dropbox->getFolderList();

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Received request to list folders.';
				$response['response']['content'] = array('dropboxResponse' => $folderData, );

			} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem retrieving the list.';
				$response['response']['content'] = array('errorText' => $data, );
			
			} // end try
		} else {
			// if no file was sent
			$data = array( 'error_summary' => "Unable to complete request.", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "not_found", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'We received your request, but header was no data.';
			$response['response']['content'] = array('errorText' => $data, );
		
		} // end if isset

		return $response;
	}



	/**
	 * Create a new folder at specified destination
	 * 
	 * @return array An associaciative array with folder success data or error information.
	 */
	public function makeNewFolder(){

		// Check if file was uploaded
		if (isset($_POST["newFolderName"])) {

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			try {

				// Upload the file to Dropbox
				$shareData = $this->dropbox->newFolder( $_POST['newFolderName'], $_POST['newFolderPath'] );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Received request to make new folder.';
				$response['response']['content'] = array('dropboxResponse' => $shareData, );

			} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem with the path.';
				$response['response']['content'] = array('errorText' => $data, );
			
			} // end try
		} else {
			// if no file was sent
			$data = array( 'error_summary' => "Could not locate path.", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "action_stopped", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'There is no location.';
			$response['response']['content'] = array('errorText' => $data, );
		
		} // end if isset

		return $response;
	}



	/**
	 * Create a new folder at specified destination
	 * 
	 * @return array An associaciative array with folder success data or error information.
	 */
	public function moveFolder(){

		// Check if file was uploaded
		if (isset($_POST["moveFolderOldPath"])) {

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			$pathtodo = array(2);
			try {

				$t = explode( "/", $_POST["moveFolderOldPath"] );
				$t = $t[ count( $t ) -1 ];
				$pathtodo[1] = $t;
				// get old folder name
				$pathtodo[0] = $_POST['moveFolderNewPath'];
				// build new path
				$paf = implode("/", $pathtodo );
				// Upload the file to Dropbox
				$shareData = $this->dropbox->moveFile( $_POST['moveFolderOldPath'], $paf );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Received request to move a folder. $paf: '.$paf;
				$response['response']['content'] = array('dropboxResponse' => $shareData, );

			} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem moving the folder.';
				$response['response']['content'] = array('errorText' => $data, );
			
			} // end try
		} else {
			// if no file was sent
			$data = array( 'error_summary' => "Could not locate path.", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "action_stopped", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'There is no location.';
			$response['response']['content'] = array('errorText' => $data, );
		
		} // end if isset

		return $response;
	}



	/**
	 * remove folder at specified destination
	 * 
	 * @return array An associaciative array with folder success data or error information.
	 */
	public function deleteFolder(){

		// Check if file was uploaded
		if (isset($_POST["deleteFolderPath"])) {

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			try {

				// Upload the file to Dropbox
				$deletedFolder = $this->dropbox->deleteFile( $_POST['deleteFolderPath'] );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Received request to delete a folder.';
				$response['response']['content'] = array('dropboxResponse' => $deletedFolder, );

			} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem removing the folder.';
				$response['response']['content'] = array('errorText' => $data, );
			
			} // end try
		} else {
			// if no file was sent
			$data = array( 'error_summary' => "Could not locate path.", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "action_stopped", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'There is no location.';
			$response['response']['content'] = array('errorText' => $data, );
		
		} // end if isset

		return $response;
	}



	// quick test to check hiearchy
	public function test($tpath){

		return $this->dropbox->getFileMeta( $tpath );
	}

} // end class


// navigate to this page in browser
// simple test case: 
// /dropbox-webapp/webapp.uploader.php?upload=/some/known/file.txt
/*
if(isset($_GET['upload'])){

	header('Content-Type: application/json');
	$app = new webappUploader();
	$resultingIn = $app->test( stripslashes( $_GET['upload'] ));
	
	echo json_encode($resultingIn);
}
*/

$gsUpload = new webappUploader();
if (isset($_FILES["uploadFile"]) && $_POST['uploadPath']) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> uploadFile());
}
if (isset($_POST["sharePath"])) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> getUploadShare());
}
if (isset($_POST['folderList'])) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> getFolderList());
}
if (isset($_POST['newFolderName'])) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> makeNewFolder());
}
if (isset($_POST['moveFolderOldPath'])) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> moveFolder());
}
if (isset($_POST['deleteFolderPath'])) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> deleteFolder());
}
?>