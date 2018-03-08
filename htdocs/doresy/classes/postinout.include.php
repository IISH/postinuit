<?php
/**
 * The action coming from the postin page is a POST Action
 */
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

	// Checking whether all required fields are filled
	$isValid = true;
	if ( $_POST['date'] === "" ){
		$isValid = false;
	} elseif ( $_POST['their_name'] === "" && $_POST['their_organisation'] === ""){
		$isValid = false;
	} elseif ( $_POST['our_name'] === ""){
		$isValid = false;
	} elseif ( $_POST['type_of_document'] === ""){
		$isValid = false;
	} elseif ( $_POST['subject'] === ""){
		$isValid = false;
	}

	// Check if all required fields have been filled in, by using the code above
	if ( $isValid ) {

		$next = "";
		$_POST['in_out'] = 'in';

		/**
		 * The post is being saved to the database
		 */
		if ( $id == '' || $id == '0' ) {
			// NEW RECORD
			$kenmerk_of_post = Posts::uploadPost($_POST, $_FILES);

			//
			if ( isset($_POST['submitValue']) ) { // save button
				// Saves the data of the mail to the database with information to be set as not sent
				Mail::insertIntoMailLog($_POST, $kenmerk_of_post, false);
			} elseif ( isset($_POST['submitValue2']) ) { // save and mail button
				// Check if the mail has been sent before the data of the mail is adjusted to set being sent
				if(Mail::mailPost($_POST, Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk_of_post), $kenmerk_of_post)){
					// Saves the data of the mail to the database with information to be set as sent
					Mail::insertIntoMailLog($_POST, $kenmerk_of_post, true);
				}
			}

			// Set the location to go to on completion
			$next = 'zoeken.php';
		} else {
			// UPDATE RECORD
			// Check is the user has the rights to adjust the post
			if ( $oWebuser->getName() === $_POST['registered_by_name'] || $oWebuser->isBeheerder() ) {
				$kenmerk_of_post = Posts::editPost( $_POST, $_FILES);

				//
				if ( isset($_POST['submitValue']) ) { // save button
					// Updates the information of the mail to the database
					Mail::updateMail($_POST, $kenmerk_of_post);
				} elseif ( isset($_POST['submitValue2']) ) { // save and mail button
					preprint('2222');
					// Check if the mail has been sent before the data of the mail is adjusted to set being sent
					if(Mail::mailPost($_POST, Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk_of_post), $kenmerk_of_post)){
						preprint('KOMT IE HIER');
						//
						Mail::insertIntoMailLog($_POST, $kenmerk_of_post,true);

						// Updates the information of the mail to the database
						// todogcu uitgezet	Mail::updateMailSent($_POST, $kenmerk_of_post);
					}
				}

				// gets the previous location (basic search)
				// TODO TODOGCU presious_location is niet altijd set
				$next = $_SESSION['previous_location'];
			}
			// The user has no rights and will be notified on the page in case attempts are made (e.g. hacking)
			else{
				$selectedPost = $_POST;
				$submitError = "* You don't have the rights to edit this post";
				$kenmerk = $selectedPost['kenmerk'];
				$submitValue = Translations::get('lbl_update_post');
				$submitAndMailValue = Translations::get('lbl_update_and_mail_post');
				$files_belonging_to_post = Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk );
			}
		}

		// Set the Location header to the given location in the code above
die('xxxx');
		Header("Location: " . $next);
	}
	// Not all required fields have been filled with data
	else{
		$selectedPost = $_POST;
		$submitError = "* Not all fields have been filled in!";
	}
}
/**
 * The action coming from the postin page is a GET Action
 */
else if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
	// Check if the id given is not empty, thus an existing post
	if ( $id !== '' && $id !== '0' ) {
		// EXISTING

		// find record
		$selectedPost = Posts::findPostById($id);

		// find username
		$a = new User( $selectedPost['registered_by'] );
		$selectedPost['registered_by_name'] = $a->getName();

		//
		$hasRightsToEdit = ($oWebuser->getId() === $selectedPost['registered_by'] || $oWebuser->isBeheerder() ) ? true : false;

		//
		$kenmerk = $selectedPost['kenmerk'];
		$files_belonging_to_post = Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk );

		//
		$submitValue = Translations::get('lbl_update_post');
		$submitAndMailValue = Translations::get('lbl_update_and_mail_post');

		$lastTimeMailSent = Mail::getLastTimeMailed($id) ? Misc::convertDateTimeToNice(Mail::getLastTimeMailed($id)) : Translations::get('lbl_not_yet_mailed');
	} else {
		// The post is non existing, thus setting the information according to a new post
		// NEW
		$currentDate = date('y');
		$characteristicsCount = intval(Settings::get('post_characteristic_last_used_counter')) + 1;
		for ( $i = strlen($characteristicsCount); $i < 3; $i++ ) {
			$currentDate .= '0';
		}
		$kenmerk = $currentDate . $characteristicsCount;

		//
		$selectedPost['kenmerk'] = $kenmerk;
		$selectedPost['registered_by_name'] = $oWebuser->getName();
		$selectedPost['registered_by'] = $oWebuser->getId();
		$selectedPost['date'] = date("Y-m-d");
	}
}

// Check whether the date in the database is correct, otherwise adjust both date and counter for characteristic
if ( Settings::get('post_characteristic_year') !== date('y') ) {
	Settings::save('post_characteristic_year', date('y'));
	Settings::save('post_characteristic_last_used_counter', 1);
}
