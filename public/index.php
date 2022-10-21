<?php

/**
 *	Loads the bootstrap environment based upon a loader
 *	that is able to auto fetch any class, trait or interface
 *	provided their namespaced declarations match the
 *	underlying folder structure.
 */
require_once '../vendor/autoload.php';

/**
 *	Initializes session support at the very start of idle
 */
\Collei\Http\Session::capture();

/**
 *	The FileUploadRequest::hasFilesOnRequest detects whether
 *	there are files being uploaded or not. Thence, the appropriate 
 *	request handler will be loaded to deal with, so we can
 *	further proceed.
 */
$request = \Collei\Support\Runnable\Bolt::returnFrom(function(){
	if (\Collei\Http\Uploaders\FileUploadRequest::hasFilesOnRequest()) {
		return \Collei\Http\Uploaders\FileUploadRequest::capture();
	}
	return \Collei\Http\Request::capture();
});

/**
 *	Checks if it is a resourceful request, i.e., when the target is
 *	one of the static resources like .css, .js or some media
 */
\Collei\Http\Request::processResourceful($request);

/**
 *	Brings the captured request into Application dominion, in which
 *	we can start processing it.
 */
$app = \Collei\App\App::start($request);

/**
 *	Fires the whole machine, letting it run away and come back
 *	with the response.
 */
$app->run();


