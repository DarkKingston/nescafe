<?php

namespace Drupal\notification_system_dispatch_webpush\ApplePush;

use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Url;
use JWage\APNS\Certificate;
use JWage\APNS\Safari\Package;
use JWage\APNS\Safari\PackageGenerator;

/**
 * Customized version of the PackageGenerator.
 *
 * Doesn't use template files for the package, instead it generates the files
 * and images on the fly.
 *
 * @package Drupal\notification_system_dispatch_webpush\ApplePush
 */
class MyPackageGenerator extends PackageGenerator {

  /**
   * Defines the image sizes for a specific filename.
   *
   * @var int[]
   *   The keys of this array are the sizes.
   *   The values are the filenames.
   */
  public static array $imageSizes = [
    16 => [
      'icon.iconset/icon_16x16.png',
    ],
    32 => [
      'icon.iconset/icon_16x16@2x.png',
      'icon.iconset/icon_32x32.png',
    ],
    64 => [
      'icon.iconset/icon_32x32@2x.png',
    ],
    128 => [
      'icon.iconset/icon_128x128.png',
    ],
    256 => [
      'icon.iconset/icon_128x128@2x.png',
    ],
  ];

  /**
   * The Url of the webservice which should be called by Apple.
   *
   * @var string
   */
  protected string $webServiceUrl;

  /**
   * Creates a customized package generator.
   *
   * The parameter webServiceUrl was added because it doesn't exist in the
   * original library.
   *
   * Also the parameter basePushPackagePath was removed.
   *
   * @param \JWage\APNS\Certificate $certificate
   *   The certificate from apple.
   * @param string $host
   *   The host of the website that is allowed.
   * @param string $webServiceUrl
   *   The Url of the webservice which should be called by Apple.
   * @param string $websiteName
   *   The name of the website that should be displayed in the notification.
   * @param string $websitePushId
   *   The website push id from apple developer page (starts with "web.").
   */
  public function __construct(Certificate $certificate,
                              string $host,
                              string $webServiceUrl,
                              string $websiteName,
                              string $websitePushId) {
    parent::__construct($certificate, '', $host, $websiteName, $websitePushId);

    $this->webServiceUrl = $webServiceUrl;
  }

  /**
   * Puts the website.json and iconset into the destination package folder.
   *
   * Does not really copy something, instead it generates it.
   *
   * @param \JWage\APNS\Safari\Package $package
   *   The package information.
   */
  protected function copyPackageFiles(Package $package) {
    $packageDir = $package->getPackageDir();

    // Add website.json file.
    $websiteJson = $this->generateWebsiteJson($package);
    file_put_contents(sprintf('%s/%s', $packageDir, 'website.json'), $websiteJson);

    $this->generateIconset($package);
  }

  /**
   * Generates the contents of the website.json file.
   *
   * @param \JWage\APNS\Safari\Package $package
   *   The package information.
   *
   * @return false|string
   *   A json string.
   */
  protected function generateWebsiteJson(Package $package) {
    $redirectUrl = Url::fromRoute('notification_system_dispatch_webpush.apple_redirect')
      ->setAbsolute(TRUE)
      ->toString();

    return json_encode([
      "websiteName" => $this->websiteName,
      "websitePushID" => $this->websitePushId,
      "allowedDomains" => [
        "https://" . $this->host,
      ],
      "urlFormatString" => $redirectUrl . '?url=%@',
      "authenticationToken" => $package->getUserId(),
      "webServiceURL" => $this->webServiceUrl,
    ]);
  }

  protected function generateIconset(Package $package) {
    $packageDir = $package->getPackageDir();
    mkdir($packageDir . '/icon.iconset');

    /** @var \Drupal\Core\Image\ImageFactory $imageFactory */
    $imageFactory = \Drupal::service('image.factory');

    /** @var \Drupal\image\ImageEffectManager $imageEffectManager */
    $imageEffectManager = \Drupal::service('plugin.manager.image.effect');

    /** @var \Drupal\image\ImageEffectInterface $convertEffect */
    $convertEffect = $imageEffectManager->createInstance('image_convert', [
      'data' => [
        'extension' => 'png',
      ],
    ]);

    foreach (self::$imageSizes as $size => $filenames) {
      $image = $imageFactory->get($this->getIconPath());

      /** @var \Drupal\image\ImageEffectInterface $scaleAndCropEffect */
      $scaleAndCropEffect = $imageEffectManager->createInstance('image_scale_and_crop', [
        'data' => [
          'width' => $size,
          'height' => $size,
        ],
      ]);

      $scaleAndCropEffect->applyEffect($image);

      $convertEffect->applyEffect($image);

      foreach ($filenames as $filename) {
        $image->save($packageDir . '/' . $filename);
      }
    }
  }

  /**
   * Load the Icon path from the dispatcher config.
   *
   * Validates if the file exists.
   *
   * @return string
   *   The path to the icon.
   *
   * @throws \Exception
   */
  public function getIconPath(): string {
    $iconPath = \Drupal::config('notification_system_dispatch_webpush.settings')->get('icon_path');

    $logger = \Drupal::logger('notification_system_dispatch_webpush');

    if (empty($iconPath) || strlen($iconPath) == 0) {
      $logger->error('Icon is missing in dispatcher configuration');
      throw new \Exception('Icon is missing in dispatcher configuration');
    }

    if (StreamWrapperManager::getScheme($iconPath)) {
      $path = \Drupal::service('file_system')->realpath($iconPath);
    }
    else {
      $path = \Drupal::root() . DIRECTORY_SEPARATOR . $iconPath;
    }

    if (!file_exists($path)) {
      $logger->error('Icon file is not existing: ' . $path);
      throw new \Exception('Icon file is not existing: ' . $path);
    }

    return $path;
  }

}
