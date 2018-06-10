<?php

namespace rjapitest\_data;

use Modules\V2\Entities\Topic;

class TopicFixture
{
    /**
     * @return Topic
     */
    public static function createAndGet() : Topic
    {
        $user        = new Topic();
        $user->title = 'Foo Bar Baz';
        $user->save();

        return $user;
    }

    /**
     * @param $id
     */
    public static function delete($id) : void
    {
        Topic::destroy($id);
    }
}