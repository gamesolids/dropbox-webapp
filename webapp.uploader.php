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
		if (isset($_FILES["file"])) {
			// File to Upload
			$file = $_FILES['file'];

			// File object needed for new file
			$postData = array('path' => $file['tmp_name'], 'name'=> $file['name'], );

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			try {

				// Upload the file to Dropbox
				$uploadedFile = $this->dropbox->newFile( $postData, $_POST['path'] );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'The file was uploaded successfully.';
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
			$data = array( 'error_summary' => "file/not_found/", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "not_found", ), ), );

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
		if (isset($_POST["path"])) {

			$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

			try {

				// Upload the file to Dropbox
				$shareData = $this->dropbox->getSharingInfo( $_POST["path"] , true );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'The file was uploaded successfully.';
				$response['response']['content'] = array('dropboxResponse' => $shareData, );

			} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem uploading the file.';
				$response['response']['content'] = array('errorText' => $data, );
			
			} // end try
		} else {
			// if no file was sent
			$data = array( 'error_summary' => "file/not_found/", 'error' => array( '.tag' => "path", 'path' => array( '.tag' => "not_found", ), ), );

			$response['responseType'] = 'error';
			$response['response']['message'] = 'We received your request, but header was no data.';
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
if (isset($_FILES["file"]) && $_POST['path']) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> uploadFile());
} else 
if (isset($_POST["path"])) {
	header('Content-Type: application/json');
	echo json_encode($gsUpload -> getUploadShare());
}

?>