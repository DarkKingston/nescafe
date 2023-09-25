<?php

namespace Drupal\ln_srh\Services;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\MimeType\MimeTypeGuesser;
use Drupal\ln_srh\SRHConstants;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\media\MediaStorage;
use Drupal\media\OEmbed\ProviderException;

class SRHMediaUils implements SRHMediaUilsInterface {

  /**
   * @var MediaStorage
   */
  protected $mediaStorage;

  /**
   * @var FileStorage
   */
  protected $fileStorage;

  /**
   * The file system service.
   *
   * @var FileSystemInterface
   */
  protected $fileSystem;

  /**
   * @var MimeTypeGuesser
   */
  protected $fileMimeTypeGuesser;

  /**
   * SRHMediaUils constructor.
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param FileSystemInterface $file_system
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,  FileSystemInterface $file_system) {
    $this->mediaStorage = $entityTypeManager->getStorage('media');
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->fileSystem = $file_system;
    $this->fileMimeTypeGuesser = \Drupal::service('file.mime_type.guesser');
  }

  /**
   * {@inheritdoc}
   */
  public function provideMediaImage($url,$title){
    $destination = 'public://srh_recipes';
    // Check if a writable directory exists, and if not try to create it.
    // If the directory exists and is writable, avoid file_prepare_directory()
    // call and write the file to destination.
    if ($this->fileSystem->prepareDirectory($destination, $this->fileSystem::CREATE_DIRECTORY | $this->fileSystem::MODIFY_PERMISSIONS)) {
      $path_info = pathinfo($url);
      // Sanitize extension
      $extension_parts = explode('_', $path_info['extension']);
      $extension = reset($extension_parts);
      try {
        $fileName = @hash_file('md2', $url, FALSE);
        if (!$fileName) {
          // Sanitize filename
          $url = $path_info['dirname'] . '/' . urlencode($path_info['filename']) . '.' . $path_info['extension'];
          $fileName = @hash_file('md2', $url, FALSE);
        }
        if(!$fileName){
          return FALSE;
        }
        $fileName .= '.' . $extension;
      }catch (\Exception $e){
        return FALSE;
      }
      $destination .= "/{$fileName}";
      // Ensure the source file exists.
      if ($file_drupal_path = system_retrieve_file($url, $destination, FALSE, $this->fileSystem::EXISTS_REPLACE) ) {
        // If file is exactly the same, there is nothing to do.
        if ($file = $this->getFileByUri($file_drupal_path)) {
          if($media = $this->getMediaImageByFid($file->id())){
            return $media;
          }else{
            /** @var MediaInterface $media */
            if($media = $this->createMediaImage($file_drupal_path,$title,$fileName,$file)){
              return $media;
            }
          }
        }
        //Save the file into drupal managed file.
        /** @var MediaInterface $media */
        if ($media = $this->createMediaImage($file_drupal_path,$title,$fileName,FALSE)) {
          return $media;
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaImageByFid($fid){
    if($fid){
      $query = $this->mediaStorage->getQuery();
      $query
        ->condition('bundle', SRHConstants::SRH_MEDIA_IMAGE_BUNDLE)
        ->condition(SRHConstants::SRH_MEDIA_IMAGE_FIELD . '.target_id', $fid);
      $result = $query->execute();
      $medias = $this->mediaStorage->loadMultiple($result);
      return empty($result) ? FALSE : reset($medias);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileByUri($uri) {
    $files = $this->fileStorage->loadByProperties(['uri' => $uri]);
    return reset($files);
  }

  /**
   * @param $uri
   * @param $name
   * @param $filename
   * @param false $file
   * @return MediaInterface|null
   */
  private function createMediaImage($uri,$name,$filename, $file=FALSE) {
    if(!$file){
      /** @var FileInterface $file */
      $file = $this->fileStorage->create([
        'filename' => $filename,
        'filepath' => $uri,
        'filemime' => $this->fileMimeTypeGuesser->guess($uri),
        'filesize' => filesize($uri),
        'uid' => 1,
        'status' => 1,
        'timestamp' => time(),
        'uri' => $uri,
      ]);
      try {
        $file->save();
      } catch (EntityStorageException $e) {
        return NULL;
      }
    }
    /** @var MediaInterface $media_entity */
    $media_entity = $this->mediaStorage->create([
      'bundle' => SRHConstants::SRH_MEDIA_IMAGE_BUNDLE,
      'uid' => 1,
      'name' => $name,
      'status' => 1,
      SRHConstants::SRH_MEDIA_IMAGE_FIELD => [
        'target_id' => $file->id(),
        'alt' => $name,
        'title' => $name,
      ],
    ]);
    try {
      $media_entity->save();
    } catch (EntityStorageException $e) {
      return NULL;
    }
    return $media_entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaRemoteVideoByUrl($url){
    $query = $this->mediaStorage->getQuery();
    $query
      ->condition('bundle', SRHConstants::SRH_MEDIA_REMOTE_VIDEO_BUNDLE)
      ->condition(SRHConstants::SRH_MEDIA_REMOTE_VIDEO_FIELD, $url);
    $result = $query->execute();
    $medias = $this->mediaStorage->loadMultiple($result);
    return empty($result) ? FALSE : reset($medias);
  }

  /**
   * {@inheritdoc}
   */
  public function provideMediaRemoteVideo($url,$title, $provider, $thumbnailUrl = ''){
    if(!$media = $this->getMediaRemoteVideoByUrl($url)){
      $media = $this->createMediaRemoteVideo($url,$title,$provider, $thumbnailUrl);
    }
    return $media;
  }

  public function createMediaRemoteVideo($url, $title, $provider, $thumbnailUrl = ''){
    if($provider == 'youtube'){
      $url = $this->convertYoutube($url);
    }
    /** @var MediaInterface $media_entity */
    $media_entity = $this->mediaStorage->create([
      'bundle' => SRHConstants::SRH_MEDIA_REMOTE_VIDEO_BUNDLE,
      'uid' => 1,
      'name' => $title,
      'status' => 1,
      SRHConstants::SRH_MEDIA_REMOTE_VIDEO_FIELD => $url,
      SRHConstants::SRH_MEDIA_THUMBNAIL_URL_FIELD => $thumbnailUrl,
    ]);
    try {
      $media_entity->save();
    } catch (EntityStorageException $e) {
      return NULL;
    } catch (ProviderException $e){
      return NULL;
    }
    return $media_entity;
  }

  private function convertYoutube($url) {
    $matches = preg_split('/(vi\/|v%3D|v=|\/v\/|youtu\.be\/|\/embed\/)/', $url);
    if (!isset($matches[1])) {
      return $url;
    }
    $match = $matches[1];
    $split = preg_split('/[^\w-]/i', $match);
    if (!isset($split[0])) {
      return $url;
    }
    $youtubeId = $split[0];
    return "https://www.youtube.com/watch?v={$youtubeId}";
  }
}
