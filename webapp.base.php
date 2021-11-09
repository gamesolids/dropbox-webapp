<?php

namespace gamesolids;

/* make vendor libs available */
require __DIR__ . '/vendor/autoload.php';
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Exceptions\DropboxClientException;

/**
 * Base dropbox access
 */

class webappBase 
{

	// application reference
	private $app = '';
	private $dropbox = NULL;
	// the current working directory
	private $cursor = NULL;

	//
	function __construct() {
		//Configure Dropbox Application
		require 'webapp.config.php';
		$this->app = new DropboxApp( $dropbox_config['app_key'], $dropbox_config['app_secret'], $dropbox_config['access_token'] );

		//Configure Dropbox service
		$this->dropbox = new Dropbox($this->app);
	}

	/**
	 * Get/Set our current Dropbox cursor (used iterating/paginating long file lists)
	 * 
	 * @return cursor or null 
	 */
	private function getCursor() {
		
		return $this->cursor;
	}

	/**
	 * Get/Set our current Dropbox cursor (used iterating/paginating long file lists)
	 * 
	 * @param cursor $db_cursor is dropbox file cursor used in requesting pages
	 * @return cursor or null
	 */
	private function setCursor($db_cursor) {

		$this->cursor = $db_cursor;

		return $this->cursor;
	}


	/**
	 * Create a new dropbox file
	 * 
	 * @param array $file is an array `$file = array('path'=>'/path/folder', 'name'=>'filename.jpg')`
	 * @param string $destination Optional. The dropbox path where the file will be stored.
	 * @return array An associaciative array with file data or error information.
	 */
	private function newFile($file,$destination) {

		//use current location if unspecified
		$destination = is_null($destination)?$this-getPath():$destination;

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				// Creating Dropbox File from Path
				$dropboxFile = new DropboxFile($file['path']);

				// Uploading the file to Dropbox
				$uploadedFile = $this->dropbox->upload($dropboxFile, $destination .'/'. $file['name'], ['autorename' => true]);

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'A new file has been added.';
				$response['response']['content'] = array('file' => $uploadedFile->getData(), );

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem creating the file.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	}


	/**
	 * Create a new dropbox folder
	 * 
	 * @param string $name The folder you want to create
	 * @param string $destination Optional. Where you want to create the folder. Root is default.
	 * @return array An associaciative array with folder data or error information.
	 */
	private function newFolder($name,$destination) {

		//use current location if unspecified
		$destination = is_null($destination)?$this-getPath():$destination;

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				if ( $destination == "/" ) $destination = "";
				// Creating Dropbox Folder
				$folder = $this->dropbox->createFolder( $destination . "/" . $name);

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'A new folder has been created.';
				$response['response']['content'] = array('filePath' => $destination . "/" . $name, );

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem creating the folder.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	}


	/**
	 * Get dropbox data for file
	 * @param string $file The folder you want to get data on
	 * @return array An associaciative array with folder data or error information.
	 */
	private function getFileMeta( $file ) {

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				// Creating Dropbox Folder
				$result = $this->dropbox->getMetadata( $file );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'File data found.';
				$response['response']['content'] = $result->getData();

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem locating that file or folder.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	}


	/**
	 * Get sharing info if it exists, optionally make it if not.
	 * 
	 * @param string $file The file you want to check for sharing info
	 * @param string $create_share Optional. Create a shared link if it does not exist
	 * @return array An associative array with file data or error information.
	 */
	private function getSharingInfo( $file, $create_share=false ) {

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				$result = $this->dropbox->postToAPI("/sharing/list_shared_links", [ "path" => $file ]);
				$data = $result->getDecodedBody();
				$createShareResponse = array( );

				// if no share is present, and we want to make one: do it.
				if ( isset($data['links']) && count($data['links']) > 0 ){
					$createShareResponse = $data['links'][0];
				} else {
					if ( $create_share ) {

						$createShareData = $this->dropbox->postToAPI("/sharing/create_shared_link_with_settings", [
							"path" => $file
						]);

						$createShareResponse = $createShareData->getDecodedBody();

					}else{
						$createShareResponse = array( );
					}
				}
				// set message pending result
				$r_msg = "Found file sharing information.";
				if ( empty($createShareResponse) ){
					$r_msg = "The file has no sharing information available.";
				}

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = $r_msg;
				$response['response']['content'] = array('sharingInfo' => $createShareResponse, );

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem creating the share.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	}


	/**
	 * delete dropbox file
	 * 
	 * @param string $file The folder you want to remove
	 * @return array An associaciative array with folder data or error information.
	 */
	private function deleteFile( $file ) {

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				// Creating Dropbox Folder
				$result = $this->dropbox->delete( $file );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'File data found.';
				$response['response']['content'] = $result->getData();

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem locating that file or folder.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	}


	/**
	 * move a dropbox file
	 * 
	 * @param string $file The folder you want to move
	 * @param string $destination The folder you want land in
	 * @return array An associaciative array with folder data or error information.
	 */
	private function moveFile( $file, $destination ) {

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				// Creating Dropbox Folder
				$result = $this->dropbox->move( $file, $destination );

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'File data found.';
				$response['response']['content'] = $result->getData();

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem locating that file or folder.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	}


	/**
	 * get dropbox folder contents
	 * 
	 * @param string $file The folder you want to get data on
	 * @param string $destination The folder you want land in
	 * @return array An associaciative array with folder data or error information.
	 */
	private function getFolderContents( $folder, $recursive = false ) {

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				// Creating Dropbox Folder
				$result = $this->dropbox->listFolder( $folder, ['recursive' => $recursive] );

				//Fetch Items
				$items = $result->getItems();

				foreach ($items as $key => $value) {
					$response['response']['content'][$key] = $value->getData();
				}

				//If more items are available
				if ($result->hasMoreItems()) {
				    //Fetch Cusrsor for listFolderContinue()
				    $cursor = $result->getCursor();

				    //Paginate through the remaining items
				    $continueResult = $this->dropbox->listFolderContinue($cursor);
				    $continueItems = $continueResult->getItems();

					foreach ($continueItems as $key => $value) {
						$response['response']['content'][$key] = $value->getData();
					}
				}

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Folder contents found.';
				// $response['response']['content'] = $result->getData();

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem locating that file or folder.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	} // end getFolderContents


	/**
	 * returns the complete folder structure as list of paths
	 * 
	 * @return array An associaciative array with folder data or error information.
	 */
	private function getFolderList(  ) {

		$response  = array('responseType' => '','response' => array('message' => '','content' => false, ), );

		try {
				// Creating Dropbox Folder
				$result = $this->dropbox->listFolder( "", ['recursive' => true] );

				//Fetch Items
				$items = $result->getItems();

				foreach ($items as $key => $value) {
					if($value->getData()['.tag'] == "folder"){
						$response['response']['content'][$key] = $value->getData();
					}
				}

				//If more items are available
				if ($result->hasMoreItems()) {
				    //Fetch Cusrsor for listFolderContinue()
				    $cursor = $result->getCursor();

				    //Paginate through the remaining items
				    $continueResult = $this->dropbox->listFolderContinue($cursor);
				    $continueItems = $continueResult->getItems();

					foreach ($continueItems as $key => $value) {
						if($value->getData()['.tag'] == "folder"){
							$response['response']['content'][$key] = $value->getData();
						}
					}
				}

				// if still good, build our response.
				$response['responseType'] = 'success';
				$response['response']['message'] = 'Folder contents found.';
				// $response['response']['content'] = $result->getData();

		} catch (\Exception $e) {

				$data = $e->getMessage();

				// if not still good, then build that response
				$response['responseType'] = 'error';
				$response['response']['message'] = 'There was a problem locating that file or folder.';
				$response['response']['content'] = array('errorText' => $data, );
		}

		// and wha-la
		return $response;

	} // end getFolderContents

	// TODO: duplicate, rename

} // end class


// navigate to this page in browser
// simple test case: 
// /dropbox-webapp/webapp.base.php?test=/some/known/file.txt
/*
if(isset($_GET['test'])){
	header('Content-Type: application/json');
	$app = new webappBase();
	$resultingIn = $app->getSharingInfo( stripslashes( $_GET['test'] ));
	echo json_encode($resultingIn);
}
*/
// simple test case:
// /dropbox-webapp/webapp.base.php?folder=/some/folder 
/*
if(isset($_GET['folder'])){

	header('Content-Type: application/json');
	$app = new webappBase();
	$resultingIn = $app->getFolderContents( stripslashes( $_GET['folder'] ));
	
	echo json_encode($resultingIn);
}
*/
?>