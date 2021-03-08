<?php

use Illuminate\Database\Seeder;
use App\PostCategory;
use App\Post;
use App\PostTag;
use App\PostComment;
use App\PostReply;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
			1 => [
                'name' => 'Online Consulting',
                'created_by' => 1,
            ],
            
		];
		foreach ($data as $id => $item) {
			$row = PostCategory::firstOrNew([
				'id' => $id,
			]);
			$row->fill($item);
            $row->save();
        }

        $data = [
			1 => [
                'post_category_id' => 1,
                'title' => '5 Great reasons to use an Online Doctor',
                'slug' => '5-great-reasons-to-use-an-online-doctor',
                'banner_image' => '308x206_1607071292.png',
                'thumbnail_image' => '308x206_1607071292.png',
                'content' => 'Lorem ipsum dolor sit amet, consectetur em adipiscing elit, sed do eiusmod tempor.Lorem ipsum dolor sit amet, consectetur em adipiscing elit, sed do eiusmod tempor.',
                'is_verified' => 1,
                'is_viewable' => 1,
                'created_by' => 1,
            ],
            
		];
		foreach ($data as $id => $item) {
			$row = Post::firstOrNew([
				'id' => $id,
			]);
			$row->fill($item);
            $row->save();
        }

        $data = [
			1 => [
                'post_id' => 1,
                'name' => 'General',
            ],
            
		];
		foreach ($data as $id => $item) {
			$row = PostTag::firstOrNew([
				'id' => $id,
			]);
			$row->fill($item);
            $row->save();
        }

        $data = [
			1 => [
                'post_id' => 1,
                'user_id' => 4,
                'comments' => 'Great blog, thanks',
                'created_by' => 1,
            ],
            
		];
		foreach ($data as $id => $item) {
			$row = PostComment::firstOrNew([
				'id' => $id,
			]);
			$row->fill($item);
            $row->save();
        }

        $data = [
			1 => [
                'comment_id' => 1,
                'user_id' => '3',
                'reply' => 'Thanks for the feedback',
                'created_by' => 1,
            ],
            
		];
		foreach ($data as $id => $item) {
			$row = PostReply::firstOrNew([
				'id' => $id,
			]);
			$row->fill($item);
            $row->save();
        }
    }
}
