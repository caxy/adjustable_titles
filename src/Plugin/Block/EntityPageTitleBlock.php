<?php

namespace Drupal\adjustable_titles\Plugin\Block;

use Drupal\Core\Block\Plugin\Block\PageTitleBlock;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'EntityPageTitleBlock' block.
 *
 * @Block(
 *  id = "entity_page_title_block",
 *  deriver = "Drupal\adjustable_titles\Plugin\Deriver\EntityViewDeriver"
 * )
 */
class EntityPageTitleBlock extends PageTitleBlock implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * @var EntityViewBuilderInterface
   */
  private $viewBuilder;

  /**
   * @var array
   */
  private $viewModeOptions;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityViewBuilderInterface $viewBuilder, array $viewModeOptions) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $viewBuilder;
    $this->viewModeOptions = $viewModeOptions;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    list(, $derivative_id) = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 2);
    $viewBuilder = $container->get('entity_type.manager')->getHandler($derivative_id, 'view_builder');
    $viewModeOptions = $container->get('entity_display.repository')->getViewModeOptions($derivative_id);
    return new static($configuration, $plugin_id, $plugin_definition, $viewBuilder, $viewModeOptions);
  }

  public function defaultConfiguration() {
    return parent::defaultConfiguration() + ['view_mode' => 'default'];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->viewModeOptions,
      '#title' => $this->t('View mode'),
      '#default_value' => $this->configuration['view_mode'],
    ];
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
  }

  public function build() {
    $context = $this->getContext('entity');
    $entity = $context->getContextData()->getValue();
    return [
      '#type' => 'page_title',
      '#title' => $this->viewBuilder->view($entity, $this->configuration['view_mode']),
      '#theme' => 'page_title__adjustable',
    ];
  }
}
