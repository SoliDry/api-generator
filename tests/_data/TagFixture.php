<?php
namespace rjapitest\_data;

use Modules\V2\Entities\Tag;

class TagFixture
{
    /**
     * @return Tag
     */
    public static function createAndGet() : Tag
    {
        $user              = new Tag();
        $user->title  = 'Foo Bar Baz';
        $user->save();

        return $user;
    }

    /**
     * @param $id
     */
    public static function delete($id) : void
    {
        Tag::destroy($id);
    }
}