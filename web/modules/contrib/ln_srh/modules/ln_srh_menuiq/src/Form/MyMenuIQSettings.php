<?php /** @noinspection ALL */

namespace Drupal\ln_srh_menuiq\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MyMenuIQSettings extends ConfigFormBase{

  /** @var string Config settings */
  const SETTINGS = 'ln_srh_menuiq.settings';

  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  public function getFormId() {
    return 'ln_srh_menuiq_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    if (!$form_state->get('count_nutritional_tips') && $form_state->get('count_nutritional_tips') !== 0) {
      $form_state->set('count_nutritional_tips', 1);
      if($nutritional_tips = $config->get('info.nutritional_tips.tips',FALSE)){
        $form_state->set('count_nutritional_tips', count($nutritional_tips));
      }
    }
    $form['#tree'] = TRUE;
    $form['balance'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Balance'),
    ];
    $form['balance_100'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#title' => $this->t('Balance reached to 100'),
      'title' => [
        '#type' => 'textfield',
        '#title' => $this->t('Heading line'),
        '#default_value' => $config->get('balance_100.title')
      ],
      'subtitle' => [
        '#type' => 'textfield',
        '#title' => $this->t('Text'),
        '#default_value' => $config->get('balance_100.subtitle')
      ],
    ];
    $form['teaser'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#title' => $this->t('Panel Teaser'),
      'title' => [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#default_value' => $config->get('teaser.title')
      ]
    ];
    $balanceTypes = ['great' => 'Great', 'good' => 'Good'];
    $form['expanded'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#title' => $this->t('Panel Expanded'),
      'title' => [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#default_value' => $config->get('expanded.title')
      ],
      'info_button_text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Info Button Text'),
        '#default_value' => $config->get('expanded.info_button_text')
      ],
      'menu_sidedishes' => [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => $this->t('Menu Sidedishes'),
        'title' => [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#default_value' => $config->get('expanded.menu_sidedishes.title')
        ],
        'add_meal_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Add Meal Text'),
          '#default_value' => $config->get('expanded.menu_sidedishes.add_meal_text')
        ],
        'add_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Add Text'),
          '#default_value' => $config->get('expanded.menu_sidedishes.add_text'),
        ],
        'empty_category_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Empty Category Text'),
          '#default_value' => $config->get('expanded.menu_sidedishes.empty_category_text'),
        ],
        'message_success' => [
          '#type' => 'textfield',
          '#title' => $this->t('Message Success'),
          '#default_value' => $config->get('expanded.menu_sidedishes.message_success')
        ]
      ],
      'summary' => [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => $this->t('Summary'),
        'button_open_text_info' => [
          '#type' => 'textfield',
          '#title' => $this->t('Open Summary info text'),
          '#description' => $this->t('Available token @count than show sidedishes count selected.'),
          '#default_value' => $config->get('expanded.summary.button_open_text_info'),
        ],
        'button_open_text_plural' => [
          '#type' => 'textfield',
          '#title' => $this->t('Open Summary CTA text plural'),
          '#description' => $this->t('Available token @count than show sidedishes count selected.'),
          '#default_value' => $config->get('expanded.summary.button_open_text_plural'),
        ],
        'button_open_text_singular' => [
          '#type' => 'textfield',
          '#title' => $this->t('Open Summary CTA text singular'),
          '#default_value' => $config->get('expanded.summary.button_open_text_singular'),
        ],
        'button_buy_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Buy Now CTA text'),
          '#default_value' => $config->get('expanded.summary.button_buy_text')
        ],
      ]
    ];
    $form['info'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#title' => $this->t('Panel Information'),
      'about' => [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => $this->t('Want to know more about MyMenu IQ™?'),
        'title' => [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#default_value' => $config->get('info.about.title')
        ],
        'menu' => [
          '#type' => 'fieldset',
          '#tree' => TRUE,
          '#title' => $this->t('What is a balanced menu?'),
          'title' => [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            '#default_value' => $config->get('info.about.menu.title')
          ],
          'description' => [
            '#type' => 'text_format',
            '#title' => $this->t('Description'),
            '#default_value' => $config->get('info.about.menu.description.value')
          ]
        ],
        'score_mean' => [
          '#type' => 'fieldset',
          '#tree' => TRUE,
          '#title' => $this->t('What does the MyMenu IQ™ score mean ?'),
          'title' => [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            '#default_value' => $config->get('info.about.score_mean.title')
          ],
          'description' => [
            '#type' => 'text_format',
            '#title' => $this->t('Description'),
            '#default_value' => $config->get('info.about.score_mean.description.value')
          ]
        ],
      ],
      'energy_info' => [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => $this->t('Energy Information'),
        'title' => [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#default_value' => $config->get('info.energy_info.title')
        ],
        'description' => [
          '#type' => 'text_format',
          '#title' => $this->t('Description'),
          '#default_value' => $config->get('info.energy_info.description.value')
        ]
      ],
      'nutritional_tips' => [
        '#type' => 'details',
        '#title' => $this->t('Nutritional Tips'),
        '#tree' => TRUE,
        'title' => [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#default_value' => $config->get('info.nutritional_tips.title')
        ],
        'tips' => [
          '#type' => 'fieldset',
          '#title' => $this->t('Tips'),
          '#tree' => TRUE,
          '#id' => 'nutritional-tips',
        ],
        'add' => [
          '#type' => 'submit',
          '#value' => $this->t('Add Tip'),
          '#submit' => [[$this, 'addTipCallback']],
          '#weight' => 100,
          '#ajax' => [
            'callback' => [$this, 'ajaxRefreshTipsCallback'],
            'wrapper' => 'nutritional-tips',
          ],
        ],
        'remove' => [
          '#type' => 'submit',
          '#value' => $this->t('Remove Tip'),
          '#submit' => [[$this, 'removeTipCallback']],
          '#weight' => 100,
          '#ajax' => [
            'callback' => [$this, 'ajaxRefreshTipsCallback'],
            'wrapper' => 'nutritional-tips',
          ],
        ],
      ]
    ];
    $balanceTypes = ['improvement' => 'Improvement','great' => 'Great','good' => 'Good'];
    foreach ($balanceTypes as $key=>$balanceType){
      $form['balance'][$key] = [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => $this->t($balanceType),
        'title' => [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#default_value' => $config->get("balance.{$key}.title"),
        ],
        'subtitle' => [
          '#type' => 'textfield',
          '#title' => $this->t('Subtitle'),
          '#default_value' => $config->get("balance.{$key}.subtitle")
        ],
        'description' => [
          '#type' => 'text_format',
          '#title' => $this->t('Description'),
          '#default_value' => $config->get("balance.{$key}.description.value")
        ],
        'min' => [
          '#type' => 'number',
          '#title' => $this->t('Min'),
          '#min' => 0,
          '#max' => 100,
          '#default_value' => $config->get("balance.{$key}.min")
        ],
        'max' => [
          '#type' => 'number',
          '#title' => $this->t('Max'),
          '#min' => 0,
          '#max' => 100,
          '#default_value' => $config->get("balance.{$key}.max")
        ],
        'color' => [
          '#type' => 'color',
          '#title' => $this->t('Color'),
          '#default_value' => $config->get("balance.{$key}.color")
        ],
      ];
    }
    for ($i = 0; $i < $form_state->get('count_nutritional_tips'); $i++) {
      $form['info']['nutritional_tips']['tips'][$i] = [
        '#type' => 'text_format',
        '#title' => $this->t('Tip @number',['@number' => $i+1]),
        '#default_value' => $config->get("info.nutritional_tips.tips.{$i}.value")
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  public function addTipCallback(array &$form, FormStateInterface $form_state) {
    $form_state->set('count_nutritional_tips', $form_state->get('count_nutritional_tips') + 1);
    $form_state->setRebuild();
  }
  public function removeTipCallback(array &$form, FormStateInterface $form_state) {
    $count_nutritional_tips = $form_state->get('count_nutritional_tips') - 1;
    $count_nutritional_tips = $count_nutritional_tips < 0 ? 0 : $count_nutritional_tips;
    $form_state->set('count_nutritional_tips', $count_nutritional_tips);
    $form_state->setRebuild();
  }

  public function ajaxRefreshTipsCallback(array &$form, FormStateInterface $form_state) {
    return $form['info']['nutritional_tips']['tips'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $config->set('balance', $form_state->getValue('balance',[]));
    $config->set('balance_100', $form_state->getValue('balance_100',[]));
    $config->set('teaser', $form_state->getValue('teaser',[]));
    $config->set('expanded', $form_state->getValue('expanded',[]));
    $info = $form_state->getValue('info',[]);
    $tips = [];
    if($nutritional_tips = $form_state->getValue(['info','nutritional_tips','tips'],[])){
      foreach ($nutritional_tips as $nutritional_tip){
        if(!empty($nutritional_tip['value'])){
          $tips[] = $nutritional_tip;
        }
      }
      $info['nutritional_tips']['tips'] = $tips;
    }
    $config->set('info', $info);
    $config->save();
    parent::submitForm($form, $form_state);
  }
}
