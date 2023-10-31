<?php
namespace Drupal\inline_entity_form\Plugin\Field\FieldFormatter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered menu' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_menu_view",
 *   label = @Translation("Rendered menu"),
 *   description = @Translation("Display the referenced menu"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceMenuFormatter extends EntityReferenceFormatterBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs an EntityReferenceEntityFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
     * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    RendererInterface $renderer,
    BlockManagerInterface $block_manager,
    AccountInterface $current_user
  ) {
    EntityReferenceFormatterBase::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->renderer = $renderer;
    $this->blockManager = $block_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('renderer'),
      $container->get('plugin.manager.block'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
        $plugin_block = $this->blockManager->createInstance('system_menu_block:' . $entity->id(), []);
        $access_result = $plugin_block->access($this->currentUser);
        // Return empty render array if user doesn't have access.
        if (is_object($access_result) && $access_result->isAllowed()
          || is_bool($access_result) && $access_result) {
          $render = $plugin_block->build();
          // Add the cache tags/contexts.
          $this->renderer
            ->addCacheableDependency($render, $plugin_block);

          $elements[$delta] = [
            '#type' => 'inline_template',
            '#template' => '<div class="{{ menu_class }}">{{ menu }}</div>',
            '#context' => [
              'menu' => $render,
              'menu_class' => 'block-menu-' . $entity->id(),
              ],
          ];
        }
      }
      return $elements;
    }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for menu entity type.
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    return $target_type === 'menu';
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity) {
    return AccessResult::allowed();
  }
}
