<?php

namespace rjapitest\unit\extensions;


use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Modules\V2\Entities\Article;
use rjapi\extension\BaseModel;
use rjapi\extension\CacheTrait;
use rjapi\helpers\ConfigOptions;
use rjapi\helpers\SqlOptions;
use rjapi\types\ModelsInterface;
use rjapitest\_data\ArticleFixture;
use rjapitest\unit\TestCase;

/**
 * Class CacheTraitTest
 * @package rjapitest\unit\extensions
 *
 * @property ConfigOptions configOptions
 * @property SqlOptions sqlOptions
 * @property Request req
 */
class CacheTraitTest extends TestCase
{
    use CacheTrait;

    private const HASH         = '8f0072d875ea7422d2fd4387621a6be8';
    private const CACHE_TTL    = 60;
    private const METHOD_INDEX = 'index';

    private $configOptions;
    private $sqlOptions;
    private $req;
    private $item;

    public function setUp()
    {
        parent::setUp();
        $this->configOptions = new ConfigOptions();
        $this->configOptions->setCalledMethod(self::METHOD_INDEX);
        $this->sqlOptions = new SqlOptions();
        $this->req        = new Request();
        $this->item       = ArticleFixture::createAndGet();
        $this->sqlOptions->setId($this->item->id);
        $this->set(self::HASH, $this->item, self::CACHE_TTL);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_sets_and_gets_exp_val()
    {
        $val = $this->getStdCached($this->req, $this->sqlOptions);
        if (!$val instanceof BaseModel) {
            $this->assertInstanceOf(Collection::class, $val);
        } else {
            $this->assertInstanceOf(BaseModel::class, $val);
        }
    }

    /**
     * @test
     */
    public function it_gets_x_fetched()
    {
        $this->configOptions->setCacheTtl(self::CACHE_TTL);
        $this->configOptions->setIsXFetch(true);
        $val = $this->getCached($this->req, $this->sqlOptions);
        $this->assertInstanceOf(Article::class, $val);
    }

    /**
     * Params needed for internal calls
     * @param $id
     * @param array $data
     * @return Article
     */
    private function getEntity($id, array $data = ModelsInterface::DEFAULT_DATA)
    {
        return ArticleFixture::createAndGet();
    }

    public function tearDown()
    {
        ArticleFixture::delete($this->item->id);
        Redis::flushall();
        parent::tearDown();
    }
}