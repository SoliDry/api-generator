<?php

namespace SoliDryTest\Unit\Blocks;

use SoliDry\Blocks\Config;
use SoliDry\ApiGenerator;
use SoliDry\Types\ConfigInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\JwtInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;
use SoliDryTest\Unit\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigTest
 * @package rjapitest\Unit\Blocks
 * @property Config config
 */
class ConfigTest extends TestCase
{
    /** @var ApiGenerator $gen */
    private $gen;
    private $config;

    public function setUp()
    {
        parent::setUp();
        $this->gen                 = new ApiGenerator();
        $this->gen->modulesDir     = DirsInterface::MODULES_DIR;
        $this->gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $this->gen->httpDir        = DirsInterface::HTTP_DIR;
        $this->gen->version        = self::MODULE_NAME;
        $data                      = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/oas/openapi.yaml'));
        $this->gen->types          = $data[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
        $this->gen->objectProps    = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->config              = new Config($this->gen);
        $this->assertInstanceOf(ConfigInterface::class, $this->config);
    }

    /**
     * @test
     */
    public function it_creates_config()
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->config);
        $this->config->create();
        $confFile = $this->gen->formatConfigPath() . 'config.php';
        $this->assertTrue(file_exists($confFile));
        // mocking config for further usage
        $arr = include $confFile;
        // to get jwt not expired for verifying in JwtTest
        $arr[JwtInterface::JWT][ConfigInterface::ACTIVATE] = 0;
        // custom sql for CustomSqlTest
        $arr['custom_sql'] = [
            'article' => [
                'enabled'  => true,
                'query'    => 'SELECT a.id, a.title FROM article a INNER JOIN tag_article ta ON ta.article_id=a.id 
                          WHERE ta.tag_id IN (
                          SELECT id FROM tag WHERE CHAR_LENGTH(title) > :tag_len
                          ) ORDER BY a.id DESC',
                'bindings' => [
                    'tag_len' => 5,
                ]
            ],
        ];
        $str               = PhpInterface::PHP_OPEN_TAG . PhpInterface::SPACE . 'return' . PhpInterface::SPACE . var_export($arr, true) . ';';
        $fp                = fopen($confFile, 'r+');
        fwrite($fp, $str);
        fclose($fp);
    }
}