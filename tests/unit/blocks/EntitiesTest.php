<?php

namespace rjapitest\unit\blocks;

use Modules\V2\Entities\Article;
use rjapi\blocks\FormRequestModel;
use rjapi\RJApiGenerator;
use rjapi\types\DirsInterface;
use rjapitest\unit\TestCase;
use rjapi\blocks\Entities;

/**
 * Class EntitiesTest
 * @package rjapitest\unit\blocks
 *
 * @property Entities entities
 */
class EntitiesTest extends TestCase
{
    private $entities;

    public function setUp()
    {
        parent::setUp();
        $gen              = new RJApiGenerator();
        $gen->objectName  = 'Article';
        $gen->version     = 'v2';
        $gen->modulesDir  = DirsInterface::MODULES_DIR;
        $gen->entitiesDir = DirsInterface::ENTITIES_DIR;
        $gen->types       = [
            'SID'               => [
                'type'      => 'string',
                'required'  => true,
                'maxLength' => 128,
            ],
            'ArticleAttributes' => [
                'description' => 'Article attributes description',
                'type'        => 'object',
                'properties'  => [
                    'title' => [
                        'required'  => true,
                        'type'      => 'string',
                        'minLength' => 16,
                        'maxLength' => 256,
                        'facets'    => [
                            'index' => [
                                'idx_title' => 'index'
                            ]
                        ]
                    ]
                ]
            ],
            'Article'           => [
                'type'       => 'object',
                'properties' => [
                    'type'          => 'Type',
                    'id'            => 'SID',
                    'attributes'    => 'ArticleAttributes',
                    'relationships' => [
                        'type' => 'TagRelationships[] | TopicRelationships',
                    ]
                ]
            ]
        ];
        $this->entities   = new Entities($gen);
    }

    /**
     * @test
     */
    public function it_creates_entity()
    {
        $this->entities->createEntity('./tests/_output/', 'Test');
        $this->assertTrue(file_exists(self::DIR_OUTPUT. 'ArticleTest.php'));
        // check props
        require_once __DIR__ . '/../../_output/ArticleTest.php';
        $article = new Article();
        $this->assertFalse($article->incrementing);
        $this->assertFalse($article->timestamps);
        $extArticle = new class extends Article {
            public function getPrimaryKey() {
                return $this->primaryKey;
            }

            public function getTable() {
                return $this->table;
            }
        };
        $this->assertEquals('id', $extArticle->getPrimaryKey());
        $this->assertEquals('article', $extArticle->getTable());
    }

    /**
     * @test
     */
    public function it_creates_pivot_entity()
    {
        $this->assertInstanceOf(FormRequestModel::class, $this->entities);
        $this->entities->createPivot();
    }
}