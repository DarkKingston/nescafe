<?php


namespace Drupal\dsu_security_node;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dsu_security_node\Form\NodeSecuritySettingsForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SecurityMiddleware implements HttpKernelInterface {

  /**
   * The decorated kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a BanMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(HttpKernelInterface $http_kernel, ConfigFactoryInterface $config_factory) {
    $this->httpKernel = $http_kernel;
    $this->config = $config_factory->get("dsu_security_node.settings");
  }

  /**
   * @inheritDoc
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    $allowed = $this->config->get(NodeSecuritySettingsForm::FIELD_HTTP_METHOD_OVERRIDE);

    // If method overriding is not allowed:
    if (!$allowed) {
      if ($request->query->has('_method')) {
        $request->query->remove('_method');
        throw new BadRequestHttpException("Not allowed query parameter present!");
      }
      $headers = $request->headers->all();
      if (is_array($headers)) {
        foreach ($headers as $header_name => $header_value) {
          if (in_array($header_name, [
            'x-http-method-override',
            'x-http-method',
            'x-method-override',
          ])) {
            $request->headers->remove($header_name);
            throw new BadRequestHttpException("Not allowed header present!");
          }
        }
      }
    }
    return $this->httpKernel->handle($request, $type, $catch);
  }

}
