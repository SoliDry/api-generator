<?php

namespace rjapitest\unit\extensions;


use Modules\V2\Entities\Article;
use rjapi\extension\BaseRelationsTrait;
use rjapi\types\RamlInterface;
use rjapitest\_data\ArticleFixture;
use rjapitest\_data\TopicFixture;
use rjapitest\unit\TestCase;


class BaseRelationsTraitTest extends TestCase
{
    use BaseRelationsTrait;

    private $article;
    private $entity;
    private $topic;
    private $modelEntity;

    public function setUp()
    {
        parent::setUp();
        $this->article = ArticleFixture::createAndGet();
        $this->topic   = TopicFixture::createAndGet();
        $this->entity  = 'Article';
        $this->modelEntity = Article::class;
    }

    /**
     * @test
     */
    public function it_sets_relationships()
    {
        $this->setRelationships([
            RamlInterface::RAML_DATA => [
                'type'                            => 'article',
                'id'                              => $this->article->id,
                RamlInterface::RAML_RELATIONSHIPS => [
                    'topic' => [
                        'data' => ['type' => 'topic', 'id' => $this->topic->id]
                    ],
                ],
            ]], $this->article->id);
        $article = $this->getEntity($this->article->id);
        $this->assertEquals($this->topic->id, $article->topic_id);
    }

    public function tearDown()
    {
        ArticleFixture::delete($this->article->id);
        TopicFixture::delete($this->topic->id);
        parent::tearDown();
    }
}