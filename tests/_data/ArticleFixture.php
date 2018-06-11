<?php

namespace rjapitest\_data;


use Modules\V2\Entities\Article;

class ArticleFixture
{
    /**
     * @return Article
     */
    public static function createAndGet() : Article
    {
        $article                = new Article();
        $article->id            = '124ea1e595ed11225727e7730d653669';
        $article->title         = 'Foo bar Foo bar Foo bar Foo bar';
        $article->description   = 'The quick brovn fox jumped ower the lazy dogg';
        $article->url           = 'http://example.com/articles_feed_123';
        $article->topic_id      = 1;
        $article->rate          = 5.0;
        $article->status        = 'draft';
        $article->show_in_top   = 1;
        $article->date_posted   = date('Y-m-d');
        $article->time_to_live = date('H:i:s');
        $article->save();

        return $article;
    }

    public static function getCollection($params)
    {
        return Article::where($params)->get();
    }

    /**
     * @param $id
     */
    public static function delete($id) : void
    {
        Article::destroy($id);
    }

    public static function truncate()
    {
        Article::truncate();
    }
}