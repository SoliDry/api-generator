<?php

namespace SoliDryTest\Unit\Transformers;

use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Modules\V2\Entities\Article;
use Modules\V2\Http\Requests\ArticleFormRequest;
use PHPUnit\Framework\Constraint\IsType;
use SoliDry\Extension\BaseModel;
use SoliDry\Transformers\DefaultTransformer;
use SoliDryTest\Unit\TestCase;

/**
 * Class DefaultTransformerTest
 * @package rjapitest\Unit\Transformers
 *
 * @property DefaultTransformer transformer
 */
class DefaultTransformerTest extends TestCase
{
    private $transformer;

    public function setUp(): void
    {
        parent::setUp();
        $middleware        = new ArticleFormRequest();
        $this->transformer = new DefaultTransformer($middleware);
    }

    /**
     * @test
     */
    public function it_transforms_objects()
    {
        $this->assertInstanceOf(TransformerAbstract::class, $this->transformer);
        $collection = new Collection();
        $this->assertInternalType(IsType::TYPE_ARRAY, $this->transformer->transform($collection));
        $baseModel = new BaseModel();
        $this->assertInternalType(IsType::TYPE_ARRAY, $this->transformer->transform($baseModel));
    }

    /**
     * @test
     */
    public function it_includes_data()
    {
        $article = new Article();
        $article->addVisible([
            'title'       => 'Foo Bar Baz',
            'description' => 'Foo Bar Baz Foo Bar Baz Foo Bar Baz',
        ]);
        $this->assertInstanceOf(Item::class, $this->transformer->includeArticle($article));
    }
}