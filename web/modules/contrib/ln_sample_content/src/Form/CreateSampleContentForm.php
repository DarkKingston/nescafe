<?php

namespace Drupal\ln_sample_content\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ln_sample_content\Controller\SampleContentMaker;

class CreateSampleContentForm extends FormBase {

  /**
   * Sample Content maker service.
   *
   * @var array
   */
  protected $sample_content_maker;

  /**
   * ImporterForm constructor.
   *
   * @param Drupal\ln_sample_content\Controller\SampleContentMaker $sample_content_maker
   */
  public function __construct(SampleContentMaker $sample_content_maker) {
    $this->sample_content_maker = $sample_content_maker;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\Core\Form\FormBase|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ln_sample_content.sample_content_maker')
    );
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'ln_sample_content_maker_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['info'] = [
      '#type'   => 'html_tag',
      '#tag'    => 'div',
      '#value'  => '<p>'.$this->t('Need a quick way to see all your components to make frontend styling quicker and more efficient?<br><br>Then click on the button below to create a sample content page. It will load all the components you have enabled with sample content and several variations, such as different alignments, dark/light, positioning. (Note: not all variations will be shown).').'</p>',
      '#weight' => 0,
    ];
    $form['submit_button'] = [
      '#type'        => 'submit',
      '#value'       => $this->t('Create content'),
      '#description' => $this->t('Create sample content.'),
      '#weight' => 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->sample_content_maker->createNodeSampleContent();
    \Drupal::messenger()->addMessage($this->t('Content created successfully.'), 'status', TRUE);
  }
}
