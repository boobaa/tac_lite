<?php

namespace Drupal\tac_lite\Tests;

use Drupal\Core\Session\AccountInterface;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\simpletest\WebTestBase;
use Drupal\tac_lite\Entity\TACLiteScheme;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;

/**
 * Create a vocabulary, add terms, add nodes, and test TAC Lite functionality.
 *
 * @group TAC Lite
 */
class TACLiteTest extends WebTestBase {

  use TaxonomyTestTrait;
  use NodeCreationTrait;
  use EntityReferenceTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'taxonomy',
    'tac_lite',
  ];

  /**
   * A vocabulary.
   *
   * @var Vocabulary
   */
  protected $vocabulary;

  /**
   * A term denoting access for everyone.
   *
   * @var Term
   */
  protected $publicTerm;

  /**
   * A term denoting access for authenticated users only.
   *
   * @var Term
   */
  protected $privateTerm;

  /**
   * A public node which is visible for both anonymous and authenticated users.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $publicNode;

  /**
   * A private node visible only for authenticated users.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $privateNode;

  /**
   * An untagged node visible only for authenticated users.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $untaggedNode;

  /**
   * A user with permission to view nodes tagged as private.
   *
   * @var AccountInterface
   */
  protected $webUser;

  /**
   * An admin user with permission to set up things.
   *
   * @var AccountInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /**
     * Test plan - setup:
     *
     * - Create a web user with 'access content' permission.
     * - Create an admin user with 'administer tac_lite' permission.
     * - Create a 'Visibility' vocabulary.
     * - Create two terms in it:
     *   - 'Public'
     *   - 'Private'
     * - Create a 'Page' content type.
     * - Add a 'Reference'/'Taxonomy term' field to it:
     *   - with the label 'Visibility',
     *   - with 'Limited'/'1' allowed number of values,
     *   - with the 'Visibility' vocabulary as the only available one.
     * - Enable TAC Lite for the 'Visibility' vocabulary.
     * - Create a TAC Lite Scheme that grants 'view' permission:
     *   - for anonymous users: only to nodes with the 'Public' term,
     *   - for authenticated users: both for the 'Public' and 'Private'
     *     terms.
     * - Create an untagged node without any term.
     * - Create a public node with the 'Public' term.
     * - Create a private node with the 'Private' term.
     */
    // Create users.
    $this->webUser = $this->drupalCreateUser(['access content']);
    $this->adminUser = $this->drupalCreateUser(['administer tac_lite']);

    // Taxonomy setup.
    $this->vocabulary = $this->createVocabulary();
    $this->publicTerm = $this->createTerm($this->vocabulary, ['name' => 'Public']);
    $this->privateTerm = $this->createTerm($this->vocabulary, ['name' => 'Private']);

    // Content structure setup.
    $this->drupalCreateContentType(['type' => 'page']);
    $this->createEntityReferenceField(
      'node',
      'page',
      'field_visibility',
      'Visibility',
      'taxonomy_term',
      'default',
      ['target_bundles' => [$this->vocabulary->id() => $this->vocabulary->id()]]
    );

    // TAC Lite setup.
    $this->drupalLogin($this->adminUser);
    $this->drupalPostForm('admin/people/access/tac_lite', [
      'categories[]' => [$this->vocabulary->id()],
    ], t('Save configuration'));
    $scheme = TACLiteScheme::create([
      'id' => 'visibility',
      'label' => 'visibility',
      'permissions' => ['grant_view' => 'grant_view'],
      'term_visibility' => TRUE,
      'grants' => [
        AccountInterface::ANONYMOUS_ROLE => [
          $this->vocabulary->id() => [
            $this->publicTerm->id() => $this->publicTerm->id(),
          ],
        ],
        AccountInterface::AUTHENTICATED_ROLE => [
          $this->vocabulary->id() => [
            $this->publicTerm->id() => $this->publicTerm->id(),
            $this->privateTerm->id() => $this->privateTerm->id(),
          ],
        ],
      ],
    ]);
    $scheme->save();

    // Content setup.
    $this->untaggedNode = $this->createNode();
    $node_settings = [];
    $node_settings['field_visibility'][0]['target_id'] = $this->publicTerm->id();
    $this->publicNode = $this->createNode($node_settings);
    $node_settings = [];
    $node_settings['field_visibility'][0]['target_id'] = $this->privateTerm->id();
    $this->privateNode = $this->createNode($node_settings);
    $this->drupalLogout();
  }

  /**
   * Tests TAC Lite functionality through node interfaces.
   */
  function testTACLite() {
    /**
     * Test plan - actual tests.
     * - As anonymous, visit untagged node - should return 200.
     * - As anonymous, visit public node - should return 200.
     * - As anonymous, visit private node - should return 403.
     * - As authenticated, visit untagged node - should return 200.
     * - As authenticated, visit public node - should return 200.
     * - As authenticated, visit private node - should return 200.
     */
    $this->drupalGet('node/' . $this->untaggedNode->id());
    $this->assertResponse(200);
    $this->drupalGet('node/' . $this->publicNode->id());
    $this->assertResponse(200);
    $this->drupalGet('node/' . $this->privateNode->id());
    $this->assertResponse(403);

    $this->drupalLogin($this->webUser);
    $this->drupalGet('node/' . $this->untaggedNode->id());
    $this->assertResponse(200);
    $this->drupalGet('node/' . $this->publicNode->id());
    $this->assertResponse(200);
    $this->drupalGet('node/' . $this->privateNode->id());
    $this->assertResponse(200);
  }

}
