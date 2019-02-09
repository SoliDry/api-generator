<?php

namespace SoliDryTest\Unit\Extensions;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\V2\Entities\Article;
use Modules\V2\Entities\Menu;
use PHPUnit\Framework\Constraint\IsType;
use SoliDry\Extension\BaseModelTrait;
use SoliDry\Extension\CustomSql;
use SoliDry\Helpers\SqlOptions;
use SoliDryTest\_data\ArticleFixture;
use SoliDryTest\_data\MenuFixture;
use SoliDryTest\Unit\TestCase;

class BaseModelTraitTest extends TestCase
{
    use BaseModelTrait;

    private $article;
    private $modelEntity;
    private $customSql;
    private $isTree = false;

    public function setUp()
    {
        parent::setUp();
        $this->article = ArticleFixture::createAndGet();
        $this->modelEntity = Article::class;
    }

    /**
     * @test
     */
    public function it_gets_entities()
    {
        $this->assertInstanceOf(Article::class, $this->getEntity($this->article->id, ['title']));
        $sqlOptions = new SqlOptions();
        $sqlOptions->setLimit(10);
        // test custom sql from config
        $this->customSql = new CustomSql('article');
        $sqlOptions->setOrderBy(['title' => 'desc']);
        $collection = $this->getEntities($sqlOptions);
        $this->assertInstanceOf(Collection::class, $collection);
        // test dynamic sql
        $this->customSql = new CustomSql('user');
        $sqlOptions->setOrderBy(['title' => 'desc']);
        $collection = $this->getEntities($sqlOptions);
        $this->assertInstanceOf(Collection::class, $collection);
    }

    /**
     * @test
     */
    public function it_gets_tree()
    {
        $menu = MenuFixture::createAndGet();
        $this->isTree = true;
        $this->modelEntity = Menu::class;
        $sqlOptions = new SqlOptions();
        $sqlOptions->setLimit(10);
        $sqlOptions->setOrderBy(['id' => 'asc']);
        // test tree algorithm
        $tree = $this->getAllTreeEntities($sqlOptions);
        $this->assertInstanceOf(Collection::class, $tree);
        $this->assertNotEmpty($tree[0]->children);
        // test sub tree algorithm
        $subTree = $this->getSubTreeEntities($sqlOptions, $menu->id);
        $this->assertInternalType(IsType::TYPE_ARRAY, $subTree);
        $this->assertNotEmpty($tree[0]->children);
        MenuFixture::truncate();
    }

    /**
     * @test
     */
    public function it_gets_model_entities()
    {
        // get 1 entity
        $article = $this->getModelEntity($this->modelEntity, $this->article->id);
        $this->assertEquals($this->article->title, $article->title);
        $this->assertEquals($this->article->topic_id, $article->topic_id);
        $this->assertEquals($this->article->status, $article->status);
        // get n entities
        $articles = $this->getModelEntities($this->modelEntity, [['topic_id', '>=', 1]]);
        $this->assertInstanceOf(Builder::class, $articles);
    }

    public function tearDown()
    {
        ArticleFixture::delete($this->article->id);
        parent::tearDown();
    }
}