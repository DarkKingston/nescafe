<?php
//namespace Drupal\Tests\dsu_multitheme;
//
//use Drupal\Core\Routing\RouteMatch;
//use Drupal\dsu_multitheme\Theme\MultipleThemeNegotiator;
//use Drupal\system\Tests\Routing\MockAliasManager;
//use Drupal\Tests\UnitTestCase;
//use Symfony\Cmf\Component\Routing\RouteObjectInterface;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Routing\Route;
//
//
///**
// * Class MultipleThemeNegotiatorTest
// * @package Drupal\Tests\dsu_multitheme
// *version 1.2
// * Test the functionality needed to return a theme based on the page url
// */
//class MultipleThemeNegotiatorTest extends UnitTestCase {
//
//
//  /** first alias that has been mapped to a theme by module */
//  private $configuredNodeId = '1';
//  private $configuredAlias  = '/products';
//  private $configuredTheme  = 'bartik';
//
//  /** second alias that has been mapped to a theme by module */
//
//  private $configuredNodeId2 = '11';
//  private $configuredAlias2  = '/articles';
//  private $configuredTheme2  = 'foo';
//
//  private $nullConfiguredNode = NULL;
//  private $nullConfiguredURL  = '/article-list';
//  private $nullConfiguredTheme  = 'bar';
//
//
//
//
//
//  /**  alias that has not been mapped to a theme by module but exists inside Drupal */
//  private $unConfiguredNodeId = '200';
//  private $unConfiguredAlias = '/test';
//
//
//  /** @var MultipleThemeNegotiator
//   * the object to be tested
//   */
//  private $multiThemeNeogitator  ;
//
//
//  public function setUp() {
//
//    $configValues =  array(
//      $this->configuredAlias => $this->configuredTheme,
//      $this->configuredAlias2 => $this->configuredTheme2,
//      $this->nullConfiguredURL => $this->nullConfiguredTheme
//    );
//
//    $config= $this->getMockBuilder('\Drupal\Core\Config\Config')
//      ->disableOriginalConstructor()
//      ->getMock();
//    $config->expects($this->any())
//      ->method('get')
//      ->with(MultipleThemeNegotiator::THEME_MAPPING_KEY)
//      ->willReturn($configValues);
//
//    $configFactory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
//      ->disableOriginalConstructor()
//      ->getMock();
//    $configFactory->expects($this->any())
//      ->method('get')
//      ->with(MultipleThemeNegotiator::SETTINGS_KEY)
//      ->willReturn($config);
//
//    $adminContext = $this->getMockBuilder('\Drupal\Core\Routing\AdminContext')
//      ->disableOriginalConstructor()
//      ->getMock();
//    $adminContext->expects($this->any())
//      ->method('isAdminRoute')
//      ->willReturn(false);
//
//    $currentPathStack = $this->getMockBuilder('Drupal\Core\Path\CurrentPathStack')
//      ->disableOriginalConstructor()
//      ->getMock();
//    $currentPathStack->expects($this->any())
//      ->method('getPath')
//      ->willReturn($this->nullConfiguredURL);
//
//
//
//    $pathAliasManager = new MockAliasManager();
//    $pathAliasManager->addAlias('/node/'.$this->configuredNodeId,$this->configuredAlias);
//    $pathAliasManager->addAlias('/node/'.$this->configuredNodeId2,$this->configuredAlias2);
//    $pathAliasManager->addAlias('/node/'.$this->unConfiguredNodeId,$this->unConfiguredAlias);
//
//    $this->multiThemeNeogitator = new MultipleThemeNegotiator($configFactory,$pathAliasManager,$adminContext,$currentPathStack);
//
//    parent::setUp();
//  }
//
//
//
//  protected function getTestRouteMatch($nodeId)
//  {
//
//    $route = new Route("/node/{node}");
//    $request = new Request();
//    $request->attributes->set(RouteObjectInterface::ROUTE_NAME,'test-route-'.rand());
//    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route);
//    if(!empty($nodeId))
//    {
//      $request->attributes->set('node',$this->getMockNode($nodeId));
//
//    }
//
//    $routeMatch = RouteMatch::createFromRequest($request);
//
//    return $routeMatch;
//  }
//
//
//  protected function getMockNode($nodeId)
//  {
//    $mockNode = $this->getMockBuilder('Drupal\node\Entity\Node')
//      ->disableOriginalConstructor()
//      ->getMock();
//    $mockNode->expects($this->any())
//      ->method('id')
//      ->willReturn($nodeId);
//    return $mockNode;
//
//  }
//
//  public function providerTestApplies() {
//    $data = array();
//    $data['unMappedRoute'] = [$this->unConfiguredNodeId, FALSE];
//    $data['mappedRoute1'] = [$this->configuredNodeId, TRUE];
//    $data['mappedRoute2'] = [$this->configuredNodeId2, TRUE];
//    $data['mappedRoute3'] = [$this->nullConfiguredNode, TRUE];
//    return $data;
//  }
//
//  public function providerTestDetermineActiveTheme() {
//    $data = array();
//    $data['unMappedRoute'] = [$this->unConfiguredNodeId, null];
//    $data['mappedRoute1'] = [$this->configuredNodeId, $this->configuredTheme];
//    $data['mappedRoute2'] = [$this->configuredNodeId2, $this->configuredTheme2];
//    $data['mappedRoute3'] = [$this->nullConfiguredNode, $this->nullConfiguredTheme];
//
//    return $data;
//  }
//
//  /**
//   * @covers \Drupal\dsu_multitheme\Theme\MultipleThemeNegotiator::applies
//   * @dataProvider providerTestApplies
//   *
//   *  see data providers here:https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
//
//   */
//  public function testApplies($nodeId,$expected) {
//    $productsRoutePath = $this->getTestRouteMatch($nodeId);
//    $this->assertSame($expected,$this->multiThemeNeogitator->applies($productsRoutePath));
//  }
//
//  /**
//   * @covers \Drupal\dsu_multitheme\Theme\MultipleThemeNegotiator::determineActiveTheme
//   * @dataProvider providerTestDetermineActiveTheme
//   *
//   *  see data providers here:https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
//
//   */
//  public function testDetermineActiveTheme($nodeId,$expected) {
//    $productsRoutePath = $this->getTestRouteMatch($nodeId);
//    $this->assertSame($expected,$this->multiThemeNeogitator->determineActiveTheme($productsRoutePath));
//  }
//}
