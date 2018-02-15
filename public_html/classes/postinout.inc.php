<?php
function createPostInOutContent( $inOrOut ) {
	global $oWebuser, $twig, $protect;

	// get id from the url
	$id = $protect->requestPositiveNumberOrEmpty('get', 'ID');
	$kenmerk = null;
	$submitValue = Translations::get('lbl_submit_post');
	$submitAndMailValue = Translations::get('lbl_submit_and_mail_post_' . $inOrOut);
	$selectedPost = array();
	$submitError = array();
	$submitWarning = array();
	$hasRightsToEdit = true;
	$lastTimeMailSent = Translations::get('lbl_not_yet_mailed');
	$recordIsFound = true;

	/**
	 * The action coming from the postin page is a POST Action
	 */
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

		// Checking whether all required fields are filled
		if ( !isset($_POST['date']) || $_POST['date'] === "" ){
			$submitError[] = Translations::get('lbl_post_date_in') . ' ' . Translations::get('is_required');
		}
		if ( ( !isset($_POST['their_name']) || $_POST['their_name'] === "" ) && ( !isset($_POST['their_organisation']) || $_POST['their_organisation'] === "" ) ) {
			if ( $inOrOut == 'in' ) {
				// als post in dan
				$submitError[] = Translations::get('lbl_post_sender_name') . ' ' . Translations::get('or') . ' ' . Translations::get('lbl_post_sender_organisation') . ' ' . Translations::get('is_required');
			} else {
				// als post uit dan
				$submitError[] = Translations::get('lbl_post_receiver_name') . ' ' . Translations::get('or') . ' ' . Translations::get('lbl_post_receiver_organisation') . ' ' . Translations::get('is_required');
			}
		}
		if ( !isset($_POST['our_name']) || $_POST['our_name'] === "" ) {
			if ( $inOrOut == 'in' ) {
				// als post in dan
				$submitError[] = Translations::get('lbl_post_receiver_name') . ' ' . Translations::get('is_required');
			} else {
				// als post uit dan
				$submitError[] = Translations::get('lbl_post_sender_name') . ' ' . Translations::get('is_required');
			}
		}
		if ( !isset($_POST['type_of_document']) || $_POST['type_of_document'] === "" ) {
			$submitError[] = Translations::get('lbl_post_document_type') . ' ' . Translations::get('is_required');
		}
		if ( !isset($_POST['subject']) || $_POST['subject'] === "" ) {
			$submitError[] = Translations::get('lbl_post_subject') . ' ' . Translations::get('is_required');
		}

		// Check if all required fields have been filled in, by using the code above
		if ( count( $submitError ) == 0 ) {

			$_POST['in_out'] = $inOrOut;

			/**
			 * The post is being saved to the database
			 */
			if ( $id == '' || $id == '0' ) {
				// SAVE NEW RECORD
				$kenmerk_of_post = Posts::uploadPost($_POST, $_FILES); // SAVE

				//
				if ( isset($_POST['submitValue2']) ) { // save and mail button
					// try to mail
					if ( count( Misc::getListOfFiles( IniSettings::get('settings', 'attachment_directory') . $kenmerk_of_post ) ) == 0 ) {
						$submitWarning[] = Translations::get('cannot_mail_if_no_attachments');
					} else {
						$sending = Mail::sendEmailPost($_POST, $kenmerk_of_post);
						if ( $sending == 1) {
							// saves the data of the mail to the database with information to be set as sent
							Mail::insertIntoMailLog($_POST);
							Mail::updateMailSent($kenmerk_of_post);
						} elseif ( $sending == 2) {
							$submitWarning[] = Translations::get('cannot_find_email_address_' . $inOrOut);
						} else {
							$submitWarning[] = 'Cannot send email.';
						}
					}
				}
			} else {
				// UPDATE RECORD
				// Check is the user has the rights to adjust the post
				if ( $oWebuser->getName() === $_POST['registered_by_name'] || $oWebuser->isBeheerder() ) {
					// UPDATE EXISTING RECORD
					$kenmerk_of_post = Posts::editPost( $_POST, $_FILES); // SAVE

					//
					if ( isset($_POST['submitValue2']) ) { // save and mail button
						// try to mail
						if ( count( Misc::getListOfFiles( IniSettings::get('settings', 'attachment_directory') . $kenmerk_of_post ) ) == 0 ) {
							$submitWarning[] = Translations::get('cannot_mail_if_no_attachments');
						} else {
							$sending = Mail::sendEmailPost($_POST, $kenmerk_of_post);
							if ( $sending == 1 ) {
								// updates the information of the mail to the database
								Mail::insertIntoMailLog($_POST);
								Mail::updateMailSent($kenmerk_of_post);
							} elseif ( $sending == 2) {
								$submitWarning[] = Translations::get('cannot_find_email_address_' . $inOrOut);
							} else {
								$submitWarning[] = 'Cannot send email.';
							}
						}
					}
				}
				// The user has no rights and will be notified on the page in case attempts are made (e.g. hacking)
				else{
					$submitError[] = "* You don't have the rights to edit this post";
				}
			}

			//
			if ( count($submitError) == 0 && count($submitWarning) == 0  ) {
				// set the location header to the given location in the code above
				if ( isset($_GET['backurl']) && $_GET['backurl'] != '' ) {
					$next = $_GET['backurl'] . '#' . $id;
				} else {
					$next = 'zoeken.php';
				}

				Header("Location: " . $next);
			}
		}

		//
		if ( count($submitError) > 0 || count($submitWarning) > 0 ) {
			$selectedPost = $_POST;
			// TODOGCU temporary debug on error
			if ( !isset($selectedPost['kenmerk']) || trim($selectedPost['kenmerk']) == '' ) {
				preprint( $selectedPost );
				die('error 9514587456');
			}
			$kenmerk = $selectedPost['kenmerk']; // TODO TODOGCU BUG SOMS IS DEZE LEEG
		}
	}
	/**
	 * The action coming from the postin page is a GET Action
	 */
	else if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {

		// Check whether the date in the database is correct, otherwise adjust both date and counter for characteristic
		if ( Settings::get('post_characteristic_year') !== date('y') ) {
			Settings::save('post_characteristic_year', date('y'));
			Settings::save('post_characteristic_last_used_counter', 0);
		}

		// Check if the id given is not empty, thus an existing post
		if ( $id !== '' && $id !== '0' ) {
			// EXISTING

			// find record
			$selectedPost = Posts::findPostById($id);

			// check number of Files
			if ( $selectedPost ) {
				$numberOfFiles = Posts::getNumberOfFilesFromPost($selectedPost['kenmerk']);
				if ($selectedPost['number_of_files'] != $numberOfFiles) {
					// save new number of files
					Posts::saveNumberOfFiles($selectedPost['ID'], $numberOfFiles);
					// set new value to object
					$selectedPost['number_of_files'] = $numberOfFiles;
				}

				// find username
				$a = new User($selectedPost['registered_by']);
				$selectedPost['registered_by_name'] = $a->getName();

				//
				$hasRightsToEdit = ($oWebuser->getId() === $selectedPost['registered_by'] || $oWebuser->isBeheerder()) ? true : false;

				//
				$kenmerk = $selectedPost['kenmerk'];

				$lastTimeMailSent = Mail::getLastTimeMailed($id) ? Misc::convertDateTimeToNice(Mail::getLastTimeMailed($id)) : Translations::get('lbl_not_yet_mailed');
			} else {
				$recordIsFound = false;
			}
		} else {
			// The post is non existing, thus setting the information according to a new post
			// NEW
			$currentDate = date('y');
			$characteristicsCount = intval(Settings::get('post_characteristic_last_used_counter')) + 1;
			for ( $i = strlen($characteristicsCount); $i < (Settings::get('length_of_kenmerk')-2); $i++ ) {
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

	$renderArray = array();
	$renderArray['title'] = Translations::get('menu_post' . $inOrOut);

	if ( !$recordIsFound ) {
		$renderArray['recordIsFound'] = 0;
		$renderArray['record_not_found'] = Translations::get('record_not_found');
	} else {
		$renderArray['recordIsFound'] = 1;
	}

	$renderArray['characteristicsInfo'] = Translations::get('lbl_post_characteristic');
	$renderArray['senderNameInfo'] = Translations::get('lbl_post_sender_name');
	$renderArray['lastTimeMailed'] = Translations::get('lbl_last_time_mailed');

	$renderArray['lastTimeMailSent'] = $lastTimeMailSent;
	$renderArray['has_rights_to_edit'] = $hasRightsToEdit;
	$renderArray['submitAndMailValue'] = $submitAndMailValue;
	$renderArray['are_you_sure_delete'] = Translations::get('are_you_sure_delete');
	$renderArray['are_you_sure_undelete'] = Translations::get('are_you_sure_undelete');
	$renderArray['removed'] = Translations::get('removed');
	$renderArray['undeleted'] = Translations::get('undeleted');
	$renderArray['lbl_upload_files'] = Translations::get('lbl_upload_files');
	$renderArray['lbl_already_uploaded_files'] = Translations::get('lbl_already_uploaded_files');
	$renderArray['document_upload_comment'] = Translations::get('document_upload_comment');
	$renderArray['nr_of_files_upload'] = Translations::get('nr_of_files_upload');
	$renderArray['help_subject'] = Translations::get('help_subject');
	$renderArray['help_remarks'] = Translations::get('help_remarks');
	$renderArray['help_type_of_document'] = Translations::get('help_type_of_document');
	$renderArray['help_date'] = Translations::get('help_date');
	$renderArray['characteristicsValue'] = $kenmerk;
	$renderArray['submitValue'] = $submitValue;
	$renderArray['submitError'] = implode("<br />\n", $submitError);
	$renderArray['submitWarning'] = implode("<br />\n", $submitWarning);
	$renderArray['selectedPost'] = $selectedPost;
	$renderArray['field_is_required'] = Translations::get('field_is_required');
	$renderArray['field_is_semi_required'] = Translations::get('field_is_semi_required');
	$renderArray['typeOfDocumentInfo'] = Translations::get('lbl_post_document_type');
	$renderArray['subjectInputInfo'] = Translations::get('lbl_post_subject');
	$renderArray['commentsInputInfo'] = Translations::get('lbl_post_comments');
	$renderArray['registeredByInfo'] = Translations::get('lbl_post_registered_by');
	$renderArray['characteristicsYear'] = Settings::get('post_characteristic_year');
	$renderArray['documentTypeOptions'] = DocumentTypes::getDocumentTypes();
	$renderArray['help_sender_name'] = Translations::get('help_sender_name');
	$renderArray['help_receiver_name'] = Translations::get('help_receiver_name');
	$renderArray['documentInfo'] = Translations::get('lbl_post_documents');
	$renderArray['receiverNameInfo'] = Translations::get('lbl_post_receiver_name');
	$renderArray['dateInfo'] = Translations::get('lbl_post_date_' . $inOrOut);
	$renderArray['not_all_required_fields_have_been_filled_out'] = Translations::get('not_all_required_fields_have_been_filled_out');
	if ( $id == '' || $id == '0' ) {
		$renderArray['onder_voorbehoud'] = Translations::get('onder_voorbehoud');
	} else {
		$renderArray['onder_voorbehoud'] = '';
	}

	if ( $inOrOut == 'in' ) {
		// IN
		$renderArray['senderInstituteInfo'] = Translations::get('lbl_post_sender_organisation');
		$renderArray['receiverInstituteInfo'] = Translations::get('lbl_post_receiver_institute');
		$renderArray['receiverDepartmentInfo'] = Translations::get('lbl_post_receiver_department');
		$renderArray['field_is_semi_required_sender_name_and_institute'] = Translations::get('field_is_semi_required_sender_name_and_institute');
		$renderArray['help_sender_organisation'] = Translations::get('help_sender_organisation');
		$renderArray['will_be_mailed_to_receiver'] = Translations::get('will_be_mailed_to_receiver');
	} else {
		// OUT
		$renderArray['senderInstituteInfo'] = Translations::get('lbl_post_sender_institute');
		$renderArray['receiverInstituteInfo'] = Translations::get('lbl_post_receiver_organisation');
		$renderArray['senderDepartmentInfo'] = Translations::get('lbl_post_sender_department');
		$renderArray['field_is_semi_required_receiver_name_and_institute'] = Translations::get('field_is_semi_required_receiver_name_and_institute');
		$renderArray['help_receiver_organisation'] = Translations::get('help_receiver_organisation');
		$renderArray['will_be_mailed_to_sender'] = Translations::get('will_be_mailed_to_sender');
	}

	return $twig->render('post' . $inOrOut . '.html', $renderArray);
}