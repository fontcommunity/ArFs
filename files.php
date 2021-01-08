<?php

require __DIR__ . '/arweave.php';

/**
 * Define the number of blocks that should be read from the source file for each chunk.
 * For 'AES-128-CBC' each block consist of 16 bytes.
 * So if we read 10,000 blocks we load 160kb into memory. You may adjust this value
 * to read/write shorter or longer chunks.
 */
define('FILE_ENCRYPTION_BLOCKS', 10000);
define('ENCRYPTION_ALGORITHM', AES-128-CBC);


//uploads given file to Arweave
function _arfs_upload_file($filepath, $encryption_key = '', $fid = 0, $orginal_file_id = 0, $extras = NULL) {

  if(file_exists($source) && filesize($source)) {
    $newFilePath = $filepath;
    $path_parts = pathinfo($filepath);

    $destFile = $filepath . '.encrypt';

    
    $org_filename = $path_parts['basename'];
    
    
    //Use encrypted file
    if($encryption) {
      $encrypted_file = _arfs_encrypt_file($filepath, $encryption_key,  $destFile);
      if($encrypted_file && file_exists($encrypted_file)) {
        $newFilePath = $encrypted_file;
      }
    }

    //Create tags here 
    $type = _arfs_get_file_type($filepath);
    $tags = _arfs_file_create_tags($destFile, $fid, $type, $org_filename, '', $orginal_file_id, $extras);
    $ar_Txn = _ar_upload_file($newFilePath, $tags);

  }
}

function _arfs_get_file_type($filepath) {
  //@todo
}

function _arfs_get_encrypted_pvt_key($encryption_key, $salt){
  //@todo
  return "encrypted private key to decrypt the file";
}

/**
 * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
 * 
 * @param string $source Path to file that should be encrypted
 * @param string $key    The key used for the encryption
 * @param string $dest   File name where the encryped file should be written to.
 * @return string|false  Returns the file name that has been created or FALSE if an error occured
 */
function _arfs_encrypt_file($source, $key, $dest){
  if(file_exists($source) && filesize($source)) {

    $dest = _arfs_duplicate_file_new_name($dest);

    $key = substr(sha1($key, true), 0, 16);
    $iv = openssl_random_pseudo_bytes(16);
    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
      // Put the initialzation vector to the beginning of the file
      fwrite($fpOut, $iv);
      if ($fpIn = fopen($source, 'rb')) {
        while (!feof($fpIn)) {
          $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
          $ciphertext = openssl_encrypt($plaintext, ENCRYPTION_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv);
          // Use the first 16 bytes of the ciphertext as the next initialization vector
          $iv = substr($ciphertext, 0, 16);
          fwrite($fpOut, $ciphertext);
        }
        fclose($fpIn);
      } 
      else {
        $error = true;
      }
      fclose($fpOut);
    } 
    else {
      $error = true;
    }
    return $error ? false : $dest;
  }
  return FALSE;
}


/**
 * Dencrypt the passed file and saves the result in a new file, removing the
 * last 4 characters from file name.
 * 
 * @param string $source Path to file that should be decrypted
 * @param string $key    The key used for the decryption (must be the same as for encryption)
 * @param string $dest   File name where the decryped file should be written to.
 * @return string|false  Returns the file name that has been created or FALSE if an error occured
 */
function _arfs_decrypt_file($source, $dest, $key ) {
  if(file_exists($source) && filesize($source)) {

    $dest = _arfs_duplicate_file_new_name($dest);

    $key = substr(sha1($key, true), 0, 16);

    $error = false;
    if($fpOut = fopen($dest, 'w')) {
      if ($fpIn = fopen($source, 'rb')) {
        // Get the initialzation vector from the beginning of the file
        $iv = fread($fpIn, 16);
        while (!feof($fpIn)) {
          $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); // we have to read one block more for  decrypting than for encrypting
          $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
          // Use the first 16 bytes of the ciphertext as the next initialization vector
          $iv = substr($ciphertext, 0, 16);
          fwrite($fpOut, $plaintext);
        }
        fclose($fpIn);
      } 
      else {
        $error = true;
      }
      fclose($fpOut);
    } 
    else {
      $error = true;
    }
    return $error ? false : $dest;
  }
  return FALSE;
}


//Check if the file exisit and give new file name 
function _arfs_duplicate_file_new_name($filename) {
  if(file_exists($filename)) {
    $loop = true;
    $index = 1;
    while($loop) {
      $path_parts = pathinfo($filename);


      $new_filename = $path_parts['dirname'] . '/' $path_parts['filename'] . '_' . $index . '.' $path_parts['extension'];
      if(file_exists($new_filename)) {
        $index++;
      }
      else {
        return $new_filename;
      }
    }
  }

  return $filename;
}


function _arfs_file_name_append() {

}

//Create tags for give file
function _arfs_file_create_tags($filepath, $fid = 0, $type = '', $filename = '', 
  $encryped = '', $orginal_file_id = 0, $extra = NULL) {


  if(file_exists($filename) && filesize($filename)) {
    $tags = [];

    //Mime 
    $tags['Content-Type'] = mime_content_type($filename);

    //File name 
    if(!trim($filename)) {
      $filename = basename($filepath);
      $tags['name'] = $filename;
    }


    //File type like font, image, css etc
    if(!trim($type)) {
      $tags['type'] = $type; 
    }

    //If the file is encrypted and need to put the encryted key
    if(!trim($encryped)) {
      $tags['encryped'] = $encryped; 
    }    

    //If this is updated version of existing file
    if($orginal_file_id) {
      $tags['org_fid'] = $orginal_file_id; 
    }

    //File ID, of system
    if($fid) {
      $tags['fid'] = $fid; 
    }    

    //Extra image if needed
    if($extra) {
      $tags['extra'] = $extra; 
    }

    return $tags;

  }
  return false;
}


function _arfs_get_file_by_arql(){
  
}


//Get File URL based on filter 
function _arfs_get_file_by_property($filters) {
  //@todo everything
}