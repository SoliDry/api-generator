<?php

namespace SoliDryTest\_data;

use Faker\Factory;
use Modules\V2\Entities\Topic;

class TopicFixture
{
    /**
     * @return Topic
     */
    public static function createAndGet() : Topic
    {
        $faker = Factory::create();

        $user        = new Topic();
        $user->title = $faker->title;
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

    public static function truncate()
    {
        Topic::truncate();
    }
}