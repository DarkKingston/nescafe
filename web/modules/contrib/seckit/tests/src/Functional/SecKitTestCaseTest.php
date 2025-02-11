<?php

namespace Drupal\Tests\seckit\Functional;

use Drupal\seckit\SeckitInterface;
use Drupal\Tests\BrowserTestBase;
use Psr\Http\Message\RequestInterface;

/**
 * Functional tests for Security Kit.
 *
 * @group seckit
 */
class SecKitTestCaseTest extends BrowserTestBase {

  /**
   * Admin user for tests.
   *
   * @var object
   */
  private $admin;

  /**
   * CSP report url.
   *
   * @var string
   */
  private $reportPath;

  /**
   * Array of modules to enable.
   *
   * @var array
   */
  public static $modules = ['seckit', 'filter'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * If set all requests made with have an origin header set with its value.
   *
   * @var bool|string
   */
  protected $originHeader = FALSE;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->admin = $this->drupalCreateUser(['administer seckit']);
    $this->drupalLogin($this->admin);

    $route_provider = \Drupal::service('router.route_provider');
    $route = $route_provider->getRouteByName('seckit.report');
    // Need to remove leading slash so it is not escaped in string.
    $path = $route->getPath();
    $this->reportPath = ltrim($path, '/');

    // Inject a Guzzle middleware to generate debug output for every request
    // performed in the test.
    $client = $this->getHttpClient();
    $handler_stack = $client->getConfig('handler');
    $handler_stack->push($this->secKitRequestHeader());
  }

  /**
   * Tests disabled Content Security Policy.
   */
  public function testDisabledCsp() {
    $form['seckit_xss[csp][checkbox]'] = FALSE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('Content-Security-Policy', NULL);
    $this->assertSession()->responseHeaderEquals('X-Content-Security-Policy', NULL);
    $this->assertSession()->responseHeaderEquals('X-WebKit-CSP', NULL);
  }

  /**
   * Tests Content Security Policy with all enabled directives.
   */
  public function testCspHasAllDirectives() {
    $form = [
      'seckit_xss[csp][checkbox]' => TRUE,
      'seckit_xss[csp][vendor-prefix][x]' => TRUE,
      'seckit_xss[csp][vendor-prefix][webkit]' => TRUE,
      'seckit_xss[csp][default-src]' => '*',
      'seckit_xss[csp][script-src]' => '*',
      'seckit_xss[csp][object-src]' => '*',
      'seckit_xss[csp][style-src]' => '*',
      'seckit_xss[csp][img-src]' => '*',
      'seckit_xss[csp][media-src]' => '*',
      'seckit_xss[csp][frame-src]' => '*',
      'seckit_xss[csp][frame-ancestors]' => '*',
      'seckit_xss[csp][child-src]' => '*',
      'seckit_xss[csp][font-src]' => '*',
      'seckit_xss[csp][connect-src]' => '*',
      'seckit_xss[csp][report-uri]' => $this->reportPath,
      'seckit_xss[csp][upgrade-req]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = 'default-src *; script-src *; object-src *; style-src *; img-src *; media-src *; frame-src *; frame-ancestors *; child-src *; font-src *; connect-src *; report-uri ' . base_path() . $this->reportPath . '; upgrade-insecure-requests';
    $this->assertSession()->responseHeaderEquals('Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-WebKit-CSP', $expected);
  }

  /**
   * Tests Content Security Policy without vendor-prefixed headers.
   */
  public function testCspWithoutVendorPrefixes() {
    $form = [
      'seckit_xss[csp][checkbox]' => TRUE,
      'seckit_xss[csp][vendor-prefix][x]' => FALSE,
      'seckit_xss[csp][vendor-prefix][webkit]' => FALSE,
      'seckit_xss[csp][default-src]' => '*',
      'seckit_xss[csp][script-src]' => '*',
      'seckit_xss[csp][object-src]' => '*',
      'seckit_xss[csp][style-src]' => '*',
      'seckit_xss[csp][img-src]' => '*',
      'seckit_xss[csp][media-src]' => '*',
      'seckit_xss[csp][frame-src]' => '*',
      'seckit_xss[csp][frame-ancestors]' => '*',
      'seckit_xss[csp][child-src]' => '*',
      'seckit_xss[csp][font-src]' => '*',
      'seckit_xss[csp][connect-src]' => '*',
      'seckit_xss[csp][report-uri]' => $this->reportPath,
      'seckit_xss[csp][upgrade-req]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = 'default-src *; script-src *; object-src *; style-src *; img-src *; media-src *; frame-src *; frame-ancestors *; child-src *; font-src *; connect-src *; report-uri ' . base_path() . $this->reportPath . '; upgrade-insecure-requests';
    $this->assertSession()->responseHeaderEquals('Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-Content-Security-Policy', NULL);
    $this->assertSession()->responseHeaderEquals('X-WebKit-CSP', NULL);
  }

  /**
   * Tests Content Security Policy with X-Content-Security-Policy header.
   */
  public function testCspWithCspVendorPrefix() {
    $form = [
      'seckit_xss[csp][checkbox]' => TRUE,
      'seckit_xss[csp][vendor-prefix][x]' => TRUE,
      'seckit_xss[csp][vendor-prefix][webkit]' => FALSE,
      'seckit_xss[csp][default-src]' => '*',
      'seckit_xss[csp][script-src]' => '*',
      'seckit_xss[csp][object-src]' => '*',
      'seckit_xss[csp][style-src]' => '*',
      'seckit_xss[csp][img-src]' => '*',
      'seckit_xss[csp][media-src]' => '*',
      'seckit_xss[csp][frame-src]' => '*',
      'seckit_xss[csp][frame-ancestors]' => '*',
      'seckit_xss[csp][child-src]' => '*',
      'seckit_xss[csp][font-src]' => '*',
      'seckit_xss[csp][connect-src]' => '*',
      'seckit_xss[csp][report-uri]' => $this->reportPath,
      'seckit_xss[csp][upgrade-req]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = 'default-src *; script-src *; object-src *; style-src *; img-src *; media-src *; frame-src *; frame-ancestors *; child-src *; font-src *; connect-src *; report-uri ' . base_path() . $this->reportPath . '; upgrade-insecure-requests';
    $this->assertSession()->responseHeaderEquals('Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-WebKit-CSP', NULL);
  }

  /**
   * Tests Content Security Policy with the X-WebKit-CSP vendor-prefixed header.
   */
  public function testCspWithWebkitCspVendorPrefix() {
    $form = [
      'seckit_xss[csp][checkbox]' => TRUE,
      'seckit_xss[csp][vendor-prefix][x]' => FALSE,
      'seckit_xss[csp][vendor-prefix][webkit]' => TRUE,
      'seckit_xss[csp][default-src]' => '*',
      'seckit_xss[csp][script-src]' => '*',
      'seckit_xss[csp][object-src]' => '*',
      'seckit_xss[csp][style-src]' => '*',
      'seckit_xss[csp][img-src]' => '*',
      'seckit_xss[csp][media-src]' => '*',
      'seckit_xss[csp][frame-src]' => '*',
      'seckit_xss[csp][frame-ancestors]' => '*',
      'seckit_xss[csp][child-src]' => '*',
      'seckit_xss[csp][font-src]' => '*',
      'seckit_xss[csp][connect-src]' => '*',
      'seckit_xss[csp][report-uri]' => $this->reportPath,
      'seckit_xss[csp][upgrade-req]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = 'default-src *; script-src *; object-src *; style-src *; img-src *; media-src *; frame-src *; frame-ancestors *; child-src *; font-src *; connect-src *; report-uri ' . base_path() . $this->reportPath . '; upgrade-insecure-requests';
    $this->assertSession()->responseHeaderEquals('Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-Content-Security-Policy', NULL);
    $this->assertSession()->responseHeaderEquals('X-WebKit-CSP', $expected);
  }

  /**
   * Tests Content Security Policy with policy-uri directive.
   *
   * In this case, only policy-uri directive should be present.
   */
  public function testCspPolicyUriDirectiveOnly() {
    $this->markTestSkipped('Test/code needs to be fixed.');
    $form = [
      'seckit_xss[csp][checkbox]'    => TRUE,
      'seckit_xss[csp][vendor-prefix][x]' => TRUE,
      'seckit_xss[csp][vendor-prefix][webkit]' => TRUE,
      'seckit_xss[csp][default-src]' => '*',
      'seckit_xss[csp][script-src]'  => '*',
      'seckit_xss[csp][object-src]'  => '*',
      'seckit_xss[csp][style-src]'   => '*',
      'seckit_xss[csp][img-src]'     => '*',
      'seckit_xss[csp][media-src]'   => '*',
      'seckit_xss[csp][frame-src]'   => '*',
      'seckit_xss[csp][child-src]'   => '*',
      'seckit_xss[csp][font-src]'    => '*',
      'seckit_xss[csp][connect-src]' => '*',
      'seckit_xss[csp][report-uri]'  => $this->reportPath,
      'seckit_xss[csp][policy-uri]'  => 'http://mysite.com/csp.xml',
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = 'policy-uri http://mysite.com/csp.xml';
    $this->assertEqual($expected, $this->drupalGetHeader('Content-Security-Policy'), t('Content-Security-Policy has only policy-uri.'));
    $this->assertEqual($expected, $this->drupalGetHeader('X-Content-Security-Policy'), t('X-Content-Security-Policy has only policy-uri.'));
    $this->assertEqual($expected, $this->drupalGetHeader('X-WebKit-CSP'), t('X-WebKit-CSP has only policy-uri.'));
  }

  /**
   * Tests Content Security Policy with all directives empty.
   *
   * In this case, we should revert back to default values.
   */
  public function testCspAllDirectivesEmpty() {
    $form = [
      'seckit_xss[csp][checkbox]' => TRUE,
      'seckit_xss[csp][vendor-prefix][x]' => TRUE,
      'seckit_xss[csp][vendor-prefix][webkit]' => TRUE,
      'seckit_xss[csp][default-src]' => 'self',
      'seckit_xss[csp][script-src]' => '',
      'seckit_xss[csp][object-src]' => '',
      'seckit_xss[csp][img-src]' => '',
      'seckit_xss[csp][media-src]' => '',
      'seckit_xss[csp][style-src]' => '',
      'seckit_xss[csp][frame-src]' => '',
      'seckit_xss[csp][frame-ancestors]' => '',
      'seckit_xss[csp][child-src]' => '',
      'seckit_xss[csp][font-src]' => '',
      'seckit_xss[csp][connect-src]' => '',
      'seckit_xss[csp][report-uri]' => $this->reportPath,
      'seckit_xss[csp][upgrade-req]' => FALSE,
      'seckit_xss[csp][policy-uri]' => '',
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = "default-src self; report-uri " . base_path() . $this->reportPath;
    $this->assertSession()->responseHeaderEquals('Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-Content-Security-Policy', $expected);
    $this->assertSession()->responseHeaderEquals('X-WebKit-CSP', $expected);
  }

  /**
   * Tests Content Security Policy in report-only mode.
   */
  public function testReportOnlyCsp() {
    $form['seckit_xss[csp][checkbox]'] = TRUE;
    $form['seckit_xss[csp][vendor-prefix][x]'] = TRUE;
    $form['seckit_xss[csp][vendor-prefix][webkit]'] = TRUE;
    $form['seckit_xss[csp][report-only]'] = TRUE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderContains('Content-Security-Policy-Report-Only', 'report-uri');
    $this->assertSession()->responseHeaderContains('X-Content-Security-Policy-Report-Only', 'report-uri');
    $this->assertSession()->responseHeaderContains('X-WebKit-CSP-Report-Only', 'report-uri');
  }

  /**
   * Tests different values for Content Security Policy report-uri.
   */
  public function testCspReportUri() {
    $report_uris = [
      [
        'uri' => '//example.com/csp-report',
        'absolute' => TRUE,
        'valid' => TRUE,
      ],
      [
        'uri' => 'https://example.com/report-uri',
        'absolute' => TRUE,
        'valid' => TRUE,
      ],
      [
        'uri' => 'http://in<val>.id/url',
        'absolute' => TRUE,
        'valid' => FALSE,
      ],
      [
        'uri' => $this->reportPath,
        'absolute' => FALSE,
        'valid' => TRUE,
      ],
      [
        // This path should be accessible to all users.
        'uri' => 'filter/tips',
        'absolute' => FALSE,
        'valid' => TRUE,
      ],
      [
        'uri' => 'non-existent-path',
        'absolute' => FALSE,
        'valid' => FALSE,
      ],
      [
        // Used to test URI with leading slash.
        'uri' => '/' . $this->reportPath,
        'absolute' => FALSE,
        'valid' => TRUE,
      ],
    ];
    foreach ($report_uris as $report_uri) {
      $form['seckit_xss[csp][checkbox]'] = TRUE;
      $form['seckit_xss[csp][vendor-prefix][x]'] = TRUE;
      $form['seckit_xss[csp][vendor-prefix][webkit]'] = TRUE;
      $form['seckit_xss[csp][default-src]'] = 'self';
      $form['seckit_xss[csp][report-uri]'] = $report_uri['uri'];
      $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
      if ($report_uri['valid']) {
        $base_path = ($report_uri['absolute']) ? '' : base_path();
        $expected = 'default-src self; report-uri ' . $base_path . $report_uri['uri'];
        if (!$report_uri['absolute'] && strpos($report_uri['uri'], '/') === 0) {
          // In this case, check that the leading slash on the relative path
          // was not mistakenly turned into two leading slashes.
          $expected = 'default-src self; report-uri ' . $base_path . ltrim($report_uri['uri'], '/');
        }
        $this->assertSession()->responseHeaderEquals('Content-Security-Policy', $expected);
        $this->assertSession()->responseHeaderEquals('X-Content-Security-Policy', $expected);
        $this->assertSession()->responseHeaderEquals('X-WebKit-CSP', $expected);
      }
      else {
        if ($report_uri['absolute']) {
          $expected = 'The CSP report-uri seems absolute but does not seem to be a valid URI.';
          $uri_type = 'absolute';
        }
        else {
          $expected = 'The CSP report-uri seems relative but does not seem to be a valid path.';
          $uri_type = 'relative';
        }
        $this->assertSession()->responseContains($expected, sprintf('Invalid %s setting for CSP report-uri was rejected.', $uri_type));
      }
    }
  }

  /**
   * Tests disabled X-XSS-Protection HTTP response header.
   */
  public function testXxssProtectionIsDisabled() {
    $form['seckit_xss[x_xss][select]'] = SeckitInterface::X_XSS_DISABLE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-XSS-Protection', NULL);
  }

  /**
   * Tests set to 0 X-XSS-Protection HTTP response header.
   */
  public function testXxssProtectionIs0() {
    $form['seckit_xss[x_xss][select]'] = SeckitInterface::X_XSS_0;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-XSS-Protection', '0');
  }

  /**
   * Tests set to 1 X-XSS-Protection HTTP response header.
   */
  public function testXxssProtectionIs1() {
    $form['seckit_xss[x_xss][select]'] = SeckitInterface::X_XSS_1;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-XSS-Protection', '1');
  }

  /**
   * Tests set to 1; mode=block X-XSS-Protection HTTP response header.
   */
  public function testXxssProtectionIs1Block() {
    $form['seckit_xss[x_xss][select]'] = SeckitInterface::X_XSS_1_BLOCK;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-XSS-Protection', '1; mode=block');
  }

  /**
   * Tests HTTP Origin allows requests from the site.
   */
  public function testOriginAllowsSite() {
    $form['seckit_csrf[origin]'] = TRUE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->originHeader = \Drupal::request()->getSchemeAndHttpHost();
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests HTTP Origin allows requests from the specified source.
   *
   * Includes a single value in the whitelist.
   */
  public function testOriginAllowsSpecifiedSource() {
    $form = [
      'seckit_csrf[origin]' => TRUE,
      'seckit_csrf[origin_whitelist]' => 'http://www.example.com',
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->originHeader = 'http://www.example.com';
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests HTTP Origin allows requests from the specified source.
   *
   * Includes multiple values in the whitelist.
   */
  public function testOriginAllowsSpecifiedSourceMultiWhitelist() {
    $form = [
      'seckit_csrf[origin]' => TRUE,
      'seckit_csrf[origin_whitelist]' => 'http://www.example.com, https://www.example.com, https://example.com:8080',
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->originHeader = 'http://www.example.com';
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests HTTP Origin denies request.
   */
  public function testOriginDeny() {
    $form['seckit_csrf[origin]'] = TRUE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->originHeader = 'http://www.example.com';
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertEqual([], $_POST, t('POST is empty.'));
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests disabled X-Frame-Options HTTP response header.
   */
  public function testXframeOptionsIsDisabled() {
    $form['seckit_clickjacking[x_frame]'] = SeckitInterface::X_FRAME_DISABLE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-Frame-Options', NULL);
  }

  /**
   * Tests set to SAMEORIGIN X-Frame-Options HTTP response header.
   */
  public function testXframeOptionsIsSameOrigin() {
    $form['seckit_clickjacking[x_frame]'] = SeckitInterface::X_FRAME_SAMEORIGIN;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-Frame-Options', 'SAMEORIGIN');
  }

  /**
   * Tests set to DENY X-Frame-Options HTTP response header.
   */
  public function testXframeOptionsIsDeny() {
    $form['seckit_clickjacking[x_frame]'] = SeckitInterface::X_FRAME_DENY;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-Frame-Options', 'DENY');
  }

  /**
   * Tests set to ALLOW-FROM X-Frame-Options HTTP response header.
   */
  public function testXframeOptionsIsAllowFrom() {
    $form['seckit_clickjacking[x_frame]'] = SeckitInterface::X_FRAME_ALLOW_FROM;
    $form['seckit_clickjacking[x_frame_allow_from]'] = 'http://www.google.com';
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('X-Frame-Options', 'ALLOW-FROM http://www.google.com');
  }

  /**
   * Tests JS + CSS + Noscript protection.
   */
  public function testJsCssNoscriptCode() {
    $form['seckit_clickjacking[js_css_noscript]'] = TRUE;
    $form['seckit_clickjacking[noscript_message]'] = 'Sorry, your JavaScript is disabled.';
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    // @TODO this was duplicated from the Event subscriber, move to function
    // in .module file?
    $path = base_path() . drupal_get_path('module', 'seckit');
    $code = <<< EOT
        <script type="text/javascript" src="$path/js/seckit.document_write.js"></script>
        <link type="text/css" rel="stylesheet" id="seckit-clickjacking-no-body" media="all" href="$path/css/seckit.no_body.css" />
        <!-- stop SecKit protection -->
        <noscript>
        <link type="text/css" rel="stylesheet" id="seckit-clickjacking-noscript-tag" media="all" href="$path/css/seckit.noscript_tag.css" />
        </noscript>
EOT;
    $this->assertSession()->responseContains($code, t('JavaScript + CSS + Noscript protection code is loaded.'));
  }

  /**
   * Tests JS + CSS + Noscript protection.
   */
  public function testJsCssNoscriptMessage() {
    $form['seckit_clickjacking[js_css_noscript]'] = TRUE;
    $form['seckit_clickjacking[noscript_message]'] = 'Sorry, your JavaScript is disabled.';
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $config = \Drupal::config('seckit.settings');
    $noscript_message = $config->get('seckit_clickjacking.noscript_message');
    // @TODO this was duplicated from the Event subscriber, move to function
    // in .module file?
    $noscript_message = $noscript_message ?
        $noscript_message :
        $this->config->get('seckit_clickjacking.noscript_message');
    $code = <<< EOT
        <noscript>
        <div id="seckit-noscript-tag">
          $noscript_message
        </div>
        </noscript>
EOT;
    $this->assertSession()->responseContains($code, t('JavaScript + CSS + Noscript protection message is loaded.'));
  }

  /**
   * Tests disabled HTTP Strict Transport Security.
   */
  public function testDisabledHsts() {
    $form['seckit_ssl[hsts]'] = FALSE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('Strict-Transport-Security', NULL);
  }

  /**
   * Tests HTTP Strict Transport Security has all directives.
   */
  public function testHstsAllDirectves() {
    $form = [
      'seckit_ssl[hsts]' => TRUE,
      'seckit_ssl[hsts_max_age]' => 1000,
      'seckit_ssl[hsts_subdomains]' => 1,
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = 'max-age=1000; includeSubDomains';
    $this->assertSession()->responseHeaderEquals('Strict-Transport-Security', $expected);
  }

  /**
   * Tests disabled From-Origin.
   */
  public function testDisabledFromOrigin() {
    $form['seckit_various[from_origin]'] = FALSE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('From-Origin', NULL);
  }

  /**
   * Tests enabled From-Origin.
   */
  public function testEnabledFromOrigin() {
    $form = [
      'seckit_various[from_origin]' => TRUE,
      'seckit_various[from_origin_destination]' => 'same',
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('From-Origin', 'same');
  }

  /**
   * Tests disabled Referrer-Policy.
   */
  public function testDisabledReferrerPolicy() {
    $form['seckit_various[referrer_policy]'] = FALSE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('Referrer-Policy', NULL);
  }

  /**
   * Tests enabled Referrer-Policy.
   */
  public function testEnabledReferrerPolicy() {
    $form = [
      'seckit_various[referrer_policy]' => TRUE,
      'seckit_various[referrer_policy_policy]' => 'no-referrer-when-downgrade',
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('Referrer-Policy', 'no-referrer-when-downgrade');
  }

  /**
   * Tests disabled Expect-CT.
   */
  public function testDisabledExpectCt() {
    $form['seckit_ct[expect_ct]'] = FALSE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('Expect-CT', NULL);
  }

  /**
   * Tests Enable Expect-CT.
   */
  public function testEnableExpectCt() {
    $form = [
      'seckit_ct[expect_ct]' => TRUE,
      'seckit_ct[max_age]' => 86400,
      'seckit_ct[enforce]' => TRUE,
      'seckit_ct[report_uri]' => 'https://www.example.com/report',
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = 'max-age=86400, enforce, report-uri="https://www.example.com/report"';
    $this->assertSession()->responseHeaderEquals('Expect-CT', $expected);
  }

  /**
   * Tests disabled feature-policy.
   */
  public function testDisabledFeaturePolicy() {
    $form['seckit_fp[feature_policy]'] = FALSE;
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $this->assertSession()->responseHeaderEquals('Feature-Policy', NULL);
  }

  /**
   * Tests enabled feature-policy.
   */
  public function testEnabledFeaturePolicy() {
    $form = [
      'seckit_fp[feature_policy]' => TRUE,
      'seckit_fp[feature_policy_policy]' => "accelerometer 'none'; camera 'none'; geolocation 'none'; gyroscope 'none'; magnetometer 'none'; microphone 'none'; payment 'none'; usb 'none'",
    ];
    $this->drupalPostForm('admin/config/system/seckit', $form, t('Save configuration'));
    $expected = "accelerometer 'none'; camera 'none'; geolocation 'none'; gyroscope 'none'; magnetometer 'none'; microphone 'none'; payment 'none'; usb 'none'";
    $this->assertSession()->responseHeaderEquals('Feature-Policy', $expected);
  }

  /**
   * Adds an origin to requests if $this->originHeader is set.
   *
   * @return \Closure
   *   A callback that adds an origin header to the request if necessary.
   */
  protected function secKitRequestHeader() {
    return function (callable $handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        if ($this->originHeader) {
          $request = $request->withHeader('origin', $this->originHeader);
        }
        return $handler($request, $options);
      };
    };
  }

}
