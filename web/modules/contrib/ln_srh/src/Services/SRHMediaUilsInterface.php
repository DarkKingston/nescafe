<?php

namespace Drupal\ln_srh\Services;

interface SRHMediaUilsInterface {

  /**
   * @param $url
   * @param $title
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function provideMediaImage($url, $title);

  /**
   * @param $url
   * @param $title
   * @param $provider
   * @return mixed
   */
  public function provideMediaRemoteVideo($url, $title, $provider, $thumbnailUrl = '');

  /**
   * @param $fid
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function getMediaImageByFid($fid);

  /**
   * @param $uri
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function getFileByUri($uri);

  /**
   * @param $url
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function getMediaRemoteVideoByUrl($url);
}
