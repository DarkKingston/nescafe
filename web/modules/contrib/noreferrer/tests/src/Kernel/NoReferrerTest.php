<?php

namespace Drupal\Tests\noreferrer\Kernel;

use Drupal\Core\Language\Language;
use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests NoReferrer filter.
 *
 * @group No Referrer
 */
class NoReferrerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   *
   * @var string[]
   */
  protected static $modules = ['filter', 'noreferrer'];

  /**
   * List of filters.
   *
   * @var \Drupal\filter\Plugin\FilterInterface[]
   */
  protected $filters;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['noreferrer']);
    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filters = $bag->getAll();
  }

  /**
   * Tests the filter.
   */
  public function testNoReferrerFilter(): void {
    $filter = $this->filters['noreferrer'];
    $haystack = (string) $filter->process('<a href="http://www.example.com/">text</a>', Language::LANGCODE_NOT_SPECIFIED);
    $this->assertStringContainsString('rel="noreferrer"', $haystack);
    $haystack = (string) $filter->process('<a rel="noreferrer" href="http://www.example.com/">text</a>', Language::LANGCODE_NOT_SPECIFIED);
    $this->assertStringContainsString('rel="noreferrer"', $haystack);
    $this->assertStringNotContainsString('rel="noreferrer noreferrer"', $haystack);

    $haystack = (string) $filter->process('<a href="http://www.example.com/" target="0">text</a>', Language::LANGCODE_NOT_SPECIFIED);
    $this->assertStringContainsString('rel="noopener noreferrer"', $haystack);
    $haystack = (string) $filter->process('<a rel="noreferrer" href="http://www.example.com/" target="_blank">text</a>', Language::LANGCODE_NOT_SPECIFIED);
    $this->assertStringContainsString('rel="noopener noreferrer"', $haystack);
  }

}
