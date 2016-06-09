<?php

namespace Drupal\adjustable_titles\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityViewDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function getDerivativeDefinitions($base_plugin_definition) {
    $derivatives = array_filter($this->entityTypeManager->getDefinitions(), function (EntityTypeInterface $entity_type) {
      return $entity_type->hasViewBuilderClass();
    });

    return array_map(function (EntityTypeInterface $entity_type) use ($base_plugin_definition) {
      $return = $base_plugin_definition;
      $return['admin_label'] = $this->t('Page title (@label)', ['@label' => $entity_type->getLabel()]);
      $return['context'] = ['entity' => new ContextDefinition('entity:' . $entity_type->id())];
      return $return;
    }, $derivatives);
  }

  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }
}