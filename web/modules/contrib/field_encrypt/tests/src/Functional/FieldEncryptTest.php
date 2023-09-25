<?php

namespace Drupal\Tests\field_encrypt\Functional;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field_encrypt\Entity\EncryptedFieldValue;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Tests field encryption.
 *
 * @group field_encrypt
 */
class FieldEncryptTest extends FieldEncryptTestBase {

  use CronRunTrait;

  /**
   * Test encrypting fields.
   *
   * This test also covers changing field encryption settings when existing
   * data already exists, as well as making fields unencrypted again with
   * data unencryption support.
   */
  public function testEncryptField() {
    $this->setFieldStorageSettings(TRUE);

    // Save test entity.
    $this->createTestNode();

    $fields = $this->testNode->getFields();
    // Check field_test_single settings.
    $single_field = $fields['field_test_single'];
    $definition = $single_field->getFieldDefinition();
    $this->assertTrue($definition instanceof FieldDefinitionInterface);
    /** @var \Drupal\Core\Field\FieldConfigInterface $storage */
    $storage = $definition->getFieldStorageDefinition();
    $this->assertEquals(TRUE, $storage->getThirdPartySetting('field_encrypt', 'encrypt', FALSE));
    $this->assertEquals(['value' => 'value', 'summary' => 'summary'], array_filter($storage->getThirdPartySetting('field_encrypt', 'properties', [])));
    $this->assertEquals('encryption_profile_1', $storage->getThirdPartySetting('field_encrypt', 'encryption_profile', ''));

    // Check field_test_multi settings.
    $single_field = $fields['field_test_multi'];
    $definition = $single_field->getFieldDefinition();
    $this->assertTrue($definition instanceof FieldDefinitionInterface);
    /** @var \Drupal\Core\Field\FieldConfigInterface $storage */
    $storage = $definition->getFieldStorageDefinition();
    $this->assertEquals(TRUE, $storage->getThirdPartySetting('field_encrypt', 'encrypt', FALSE));
    $this->assertEquals(['value' => 'value'], array_filter($storage->getThirdPartySetting('field_encrypt', 'properties', [])));
    $this->assertEquals('encryption_profile_2', $storage->getThirdPartySetting('field_encrypt', 'encryption_profile', ''));

    // Check existence of EncryptedFieldValue entities.
    $encrypted_field_values = EncryptedFieldValue::loadMultiple();
    $this->assertEqual(5, count($encrypted_field_values));

    // Check if text is displayed unencrypted.
    $this->drupalGet('node/' . $this->testNode->id());
    $this->assertText("Lorem ipsum dolor sit amet.");
    $this->assertText("one");
    $this->assertText("two");
    $this->assertText("three");

    $result = \Drupal::database()->query("SELECT field_test_single_value FROM {node__field_test_single} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchField();
    $this->assertEqual("[ENCRYPTED]", $result);

    $result = \Drupal::database()->query("SELECT field_test_multi_value FROM {node__field_test_multi} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchAll();
    foreach ($result as $record) {
      $this->assertEquals("[ENCRYPTED]", $record->field_test_multi_value);
    }

    // Test updating entities with alternative encryption settings.
    $this->setFieldStorageSettings(TRUE, TRUE);
    // Update existing data with new field encryption settings.
    $this->assertLinkByHref('admin/config/system/field-encrypt/field-update');
    $this->drupalGet('admin/config/system/field-encrypt/field-update');
    $this->assertText('There are 2 fields queued for encryption updates.');
    $this->cronRun();
    $this->drupalGet('admin/config/system/field-encrypt/field-update');
    $this->assertText('There are 0 fields queued for encryption updates.');

    // Check existence of EncryptedFieldValue entities.
    $encrypted_field_values = EncryptedFieldValue::loadMultiple();
    $this->assertEqual(5, count($encrypted_field_values));

    // Check if text is displayed unencrypted.
    $this->drupalGet('node/' . $this->testNode->id());
    $this->assertText("Lorem ipsum dolor sit amet.");
    $this->assertText("one");
    $this->assertText("two");
    $this->assertText("three");

    // Check values saved in the database.
    $result = \Drupal::database()->query("SELECT field_test_single_value FROM {node__field_test_single} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchField();
    $this->assertEquals("[ENCRYPTED]", $result);

    $result = \Drupal::database()->query("SELECT field_test_multi_value FROM {node__field_test_multi} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchAll();
    foreach ($result as $record) {
      $this->assertEquals("[ENCRYPTED]", $record->field_test_multi_value);
    }

    // Test updating entities to remove field encryption.
    $this->setFieldStorageSettings(FALSE);
    // Update existing data with new field encryption settings.
    $this->assertLinkByHref('admin/config/system/field-encrypt/field-update');
    $this->drupalGet('admin/config/system/field-encrypt/field-update');
    $this->assertText('There are 2 fields queued for encryption updates.');
    $this->cronRun();
    $this->drupalGet('admin/config/system/field-encrypt/field-update');
    $this->assertText('There are 0 fields queued for encryption updates.');

    // Check removal of EncryptedFieldValue entities.
    $encrypted_field_values = EncryptedFieldValue::loadMultiple();
    $this->assertEquals(0, count($encrypted_field_values));

    // Check if text is displayed unencrypted.
    $this->drupalGet('node/' . $this->testNode->id());
    $this->assertText("Lorem ipsum dolor sit amet.");
    $this->assertText("one");
    $this->assertText("two");
    $this->assertText("three");

    $result = \Drupal::database()->query("SELECT field_test_single_value FROM {node__field_test_single} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchField();
    $this->assertEquals("Lorem ipsum dolor sit amet.", $result);

    $result = \Drupal::database()->query("SELECT field_test_multi_value FROM {node__field_test_multi} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchAll();
    $valid_values = ["one", "two", "three"];
    foreach ($result as $record) {
      $this->assertTrue(in_array($record->field_test_multi_value, $valid_values));
    }
  }

  /**
   * Test encrypting fields with revisions.
   *
   * This test also covers deletion of an encrypted field with existing data.
   */
  public function testEncryptFieldRevision() {
    $this->setFieldStorageSettings(TRUE);

    // Save test entity.
    $this->createTestNode();

    // Create a new revision for the entity.
    $old_revision_id = $this->testNode->getRevisionId();
    $this->testNode->setNewRevision(TRUE);
    $this->testNode->field_test_single->value = "Lorem ipsum dolor sit amet revisioned.";
    $this->testNode->field_test_single->summary = "Lorem ipsum revisioned.";
    $multi_field = $this->testNode->get('field_test_multi');
    $multi_field_value = $multi_field->getValue();
    $multi_field_value[0]['value'] = "four";
    $multi_field_value[1]['value'] = "five";
    $multi_field_value[2]['value'] = "six";
    $multi_field->setValue($multi_field_value);
    $this->testNode->save();

    // Ensure that the node revision has been created.
    $this->entityTypeManager->getStorage('node')->resetCache([$this->testNode->id()]);
    $this->assertNotIdentical($this->testNode->getRevisionId(), $old_revision_id, 'A new revision has been created.');

    // Check existence of EncryptedFieldValue entities.
    $encrypted_field_values = EncryptedFieldValue::loadMultiple();
    $this->assertEquals(10, count($encrypted_field_values));

    // Check if revisioned text is displayed unencrypted.
    $this->drupalGet('node/' . $this->testNode->id());
    $this->assertText("Lorem ipsum dolor sit amet revisioned.");
    $this->assertText("four");
    $this->assertText("five");
    $this->assertText("six");

    // Check if original text is displayed unencrypted.
    $this->drupalGet('node/' . $this->testNode->id() . '/revisions/' . $old_revision_id . '/view');
    $this->assertText("Lorem ipsum dolor sit amet.");
    $this->assertText("one");
    $this->assertText("two");
    $this->assertText("three");

    // Check values saved in the database.
    $result = \Drupal::database()->query("SELECT field_test_single_value FROM {node_revision__field_test_single} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchField();
    $this->assertEquals("[ENCRYPTED]", $result);

    $result = \Drupal::database()->query("SELECT field_test_multi_value FROM {node_revision__field_test_multi} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchAll();
    foreach ($result as $record) {
      $this->assertEquals("[ENCRYPTED]", $record->field_test_multi_value);
    }

    $this->drupalPostForm('admin/structure/types/manage/page/fields/node.page.field_test_multi/delete', [], 'Delete');

    // Test if EncryptedFieldValue entities got deleted.
    $encrypted_field_values = EncryptedFieldValue::loadMultiple();
    $this->assertEquals(4, count($encrypted_field_values));
  }

  /**
   * Test encrypting fields with translations.
   */
  public function testEncryptFieldTranslation() {
    $this->setTranslationSettings();
    $this->setFieldStorageSettings(TRUE);

    // Save test entity.
    $this->createTestNode();

    // Reload node after saving.
    $controller = $this->entityTypeManager->getStorage($this->testNode->getEntityTypeId());
    $controller->resetCache([$this->testNode->id()]);
    $this->testNode = $controller->load($this->testNode->id());

    // Add translated values.
    $translated_values = [
      'title' => $this->randomMachineName(8),
      'field_test_single' => [
        [
          'value' => "Ceci est un text francais.",
          'summary' => "Text francais",
          'format' => filter_default_format(),
        ],
      ],
      'field_test_multi' => [
        ['value' => "un"],
        ['value' => "deux"],
        ['value' => "trois"],
      ],
    ];
    $this->testNode->addTranslation('fr', $translated_values);
    $this->testNode->save();

    // Check existence of EncryptedFieldValue entities.
    $encrypted_field_values = EncryptedFieldValue::loadMultiple();
    $this->assertEquals(10, count($encrypted_field_values));

    // Check if English text is displayed unencrypted.
    $this->drupalGet('node/' . $this->testNode->id());
    $this->assertText("Lorem ipsum dolor sit amet.");
    $this->assertText("one");
    $this->assertText("two");
    $this->assertText("three");

    // Check if French text is displayed unencrypted.
    $this->drupalGet('fr/node/' . $this->testNode->id());
    $this->assertText("Ceci est un text francais.");
    $this->assertText("un");
    $this->assertText("deux");
    $this->assertText("trois");

    // Check values saved in the database.
    $result = \Drupal::database()->query("SELECT field_test_single_value FROM {node__field_test_single} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchAll();
    foreach ($result as $record) {
      $this->assertEquals("[ENCRYPTED]", $record->field_test_single_value);
    }

    $result = \Drupal::database()->query("SELECT field_test_multi_value FROM {node__field_test_multi} WHERE entity_id = :entity_id", [':entity_id' => $this->testNode->id()])->fetchAll();
    foreach ($result as $record) {
      $this->assertEquals("[ENCRYPTED]", $record->field_test_multi_value);
    }
  }

}
