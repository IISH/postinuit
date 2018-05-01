<?php

class File {
	public static function ensureDirectoryExists( $directory, $label ) {
        // check to see if the directory exists, otherwise create it
        if ( !file_exists( $directory ) ) {
            if ( !mkdir($directory, 0764, true ) ) {
                die("Failed to create $label directory: " . $directory);
            }
		}
	}

	// TODO TODOGCU2
	public static function uploadFilesToServer($filesDocumentInput, $kenmerk) {
		$directory_to_save = IniSettings::get('settings', 'attachment_directory') . $kenmerk . "/";

		// check to see if the array with files is empty or not
		$numberOfFiles = count($filesDocumentInput['name']);
		for ( $i = 0; $i < $numberOfFiles; $i++ ) {
			if ( $filesDocumentInput['tmp_name'][$i] != '' ) {
				$fileData = file_get_contents($filesDocumentInput['tmp_name'][$i]);

				$filename = $filesDocumentInput['name'][$i];
				if ( Settings::get('allow_overwrite_on_upload') == 1 || !file_exists($directory_to_save . $filename) ) {
					// if new or if overwrite allowed
					file_put_contents($directory_to_save . $filesDocumentInput['name'][$i], $fileData);
				} else {
					// if existing and NO overwrite
					$newFileName = File::findNewFilename($directory_to_save, $filename);
					file_put_contents($directory_to_save . $newFileName, $fileData);
				}
			}
		}
	}

	public static function findNewFilename($directory_to_save, $filename) {
		$parts = explode('.', $filename);
		$name = '';
		$extension = '';
		$filenumber = 0;

		if ( count($parts) == 1 ) {
			$name = $parts[0];
		} elseif ( count($parts) == 2 ) {
			$name = $parts[0];

			$partMin1 = $parts[count($parts)-1];

			// controleer laatste deel of het (number) is
			preg_match('/\(([0-9]+)\)/', $partMin1, $matches, PREG_OFFSET_CAPTURE);

			if ( count($matches) >= 2 ) {
				$filenumber = $matches[1][0];
				$extension = '';
			} else {
				$extension = '.' . $parts[count($parts)-1];
			}
		} else {
			$partMin2 = $parts[count($parts)-2];

			// controleer voorlaatste deel of het (number) is
			preg_match('/\(([0-9]+)\)/', $partMin2, $matches, PREG_OFFSET_CAPTURE);

			if ( count($matches) >= 2 ) {
				$filenumber = $matches[1][0];

				$separator = '';
				for ( $i = 0; $i < count($parts)-2; $i++ ) {
					$name .= $separator . $parts[$i];
					$separator = '.';
				}
			} else {
				$separator = '';
				for ( $i = 0; $i < count($parts)-1; $i++ ) {
					$name .= $separator . $parts[$i];
					$separator = '.';
				}
			}

			$extension = '.' . $parts[count($parts)-1];
		}

		// try to find next free filenumber
		do {
			$filenumber++;
		} while ( file_exists( $directory_to_save . $name . '.(' . $filenumber . ')' . $extension ) );

		//
		return $name . '.(' . $filenumber . ')' . $extension;
	}
}
