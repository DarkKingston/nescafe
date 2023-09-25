<?php

namespace Drupal\ln_seo_hreflang\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\ln_seo_hreflang\Entity\LnHreflangInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a ln_hreflang deletion confirmation form.
 */
class LnHreflangDeleteMultipleForm extends ConfirmFormBase {

  /**
   * The array of ln_hreflangs to delete.
   *
   * @var \Drupal\ln_seo_hreflang\Entity\LnHreflangInterface[]
   */
  protected $ln_hreflangs = array();

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $tempStore;

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a LnHreflangDeleteMultipleForm object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStore = $temp_store_factory->get('ln_hreflang_multiple_delete_confirm');
    $this->storage = $entity_type_manager->getStorage('ln_hreflang');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_hreflang_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return \Drupal::translation()->formatPlural(
      count($this->ln_hreflangs),
      'Are you sure you want to delete this hreflang?',
      'Are you sure you want to delete these hreflangs?'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.ln_hreflang.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->ln_hreflangs = $this->tempStore->get('delete');
    if (empty($this->ln_hreflangs)) {
      $form_state->setRedirect('entity.ln_hreflang.collection');
    }

    $form['ln_hreflangs'] = array(
      '#theme' => 'item_list',
      '#items' => array_map(function (LnHreflangInterface $ln_hreflang) {
        return Html::escape($ln_hreflang->label());
      }, $this->ln_hreflangs),
    );
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->ln_hreflangs)) {
      $this->storage->delete($this->ln_hreflangs);
      $this->tempStore->delete('delete');
      $count = count($this->ln_hreflangs);
      $this->messenger()->addMessage($this->stringTranslation->formatPlural($count, 'Deleted 1 hreflang.', 'Deleted @count hreflangs.'));
    }
    $form_state->setRedirect('entity.ln_hreflang.collection');
  }

}
